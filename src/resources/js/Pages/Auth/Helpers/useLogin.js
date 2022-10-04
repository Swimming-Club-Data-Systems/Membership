import {
    fetchEndpoint,
    preparePublicKeyOptions,
} from "@web-auth/webauthn-helper/src/common";

export function bufferToBase64URLString(buffer) {
    const bytes = new Uint8Array(buffer);
    let str = "";

    for (const charCode of bytes) {
        str += String.fromCharCode(charCode);
    }

    const base64String = btoa(str);

    return base64String
        .replace(/\+/g, "-")
        .replace(/\//g, "_")
        .replace(/=/g, "");
}

// Prepares the public key credentials object returned by the authenticator
export const preparePublicKeyCredentials = (data) => {
    const publicKeyCredential = {
        id: data.id,
        type: data.type,
        rawId: bufferToBase64URLString(data.rawId),
        response: {
            clientDataJSON: bufferToBase64URLString(
                data.response.clientDataJSON
            ),
        },
    };

    if (data.response.attestationObject !== undefined) {
        publicKeyCredential.response.attestationObject =
            bufferToBase64URLString(data.response.attestationObject);
    }

    if (data.response.authenticatorData !== undefined) {
        publicKeyCredential.response.authenticatorData =
            bufferToBase64URLString(data.response.authenticatorData);
    }

    if (data.response.signature !== undefined) {
        publicKeyCredential.response.signature = bufferToBase64URLString(
            data.response.signature
        );
    }

    if (data.response.userHandle !== undefined) {
        publicKeyCredential.response.userHandle = bufferToBase64URLString(
            data.response.userHandle
        );
    }

    return publicKeyCredential;
};

const useLogin = (
    { actionUrl = "/login", actionHeader = {}, optionsUrl = "/login/options" },
    optionsHeader = {},
    setAc = null
) => {
    // eslint-disable-next-line no-unused-vars
    return async ({ credentialsGetProps, ...data }) => {
        const optionsResponse = await fetchEndpoint(
            data,
            optionsUrl,
            optionsHeader
        );
        const json = await optionsResponse.json();
        const publicKey = preparePublicKeyOptions(json);

        if (setAc) {
            setAc("username webauthn");
        }

        const credentials = await navigator.credentials.get({
            publicKey,
            ...credentialsGetProps,
        });
        const publicKeyCredential = preparePublicKeyCredentials(credentials);
        const actionResponse = await fetchEndpoint(
            publicKeyCredential,
            actionUrl,
            actionHeader
        );
        if (!actionResponse.ok) {
            throw actionResponse;
        }
        const responseBody = await actionResponse.text();

        return responseBody !== "" ? JSON.parse(responseBody) : responseBody;
    };
};

export default useLogin;
