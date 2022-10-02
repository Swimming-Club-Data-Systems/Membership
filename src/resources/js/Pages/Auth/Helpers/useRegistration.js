import {
    fetchEndpoint,
    preparePublicKeyOptions,
} from "@web-auth/webauthn-helper/src/common";
import { preparePublicKeyCredentials } from "@/Pages/Auth/Helpers/useLogin";

const useRegistration = (
    {
        actionUrl = "/register",
        actionHeader = {},
        optionsUrl = "/register/options",
    },
    optionsHeader = {}
) => {
    return async (data) => {
        const optionsResponse = await fetchEndpoint(
            data,
            optionsUrl,
            optionsHeader
        );
        const json = await optionsResponse.json();
        const publicKey = preparePublicKeyOptions(json);
        const credentials = await navigator.credentials.create({ publicKey });
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

export default useRegistration;
