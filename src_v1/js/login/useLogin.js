import {
  fetchEndpoint,
  preparePublicKeyCredentials,
  preparePublicKeyOptions,
} from "@web-auth/webauthn-helper/src/common";

const useLogin = ({ actionUrl = "/login", actionHeader = {}, optionsUrl = "/login/options" }, optionsHeader = {}) => {
  // eslint-disable-next-line no-unused-vars
  return async ({credentialsGetProps, ...data}) => {
    const optionsResponse = await fetchEndpoint(data, optionsUrl, optionsHeader);
    const json = await optionsResponse.json();
    const publicKey = preparePublicKeyOptions(json);
    const credentials = await navigator.credentials.get({
      publicKey,
      ...credentialsGetProps
    });
    const publicKeyCredential = preparePublicKeyCredentials(credentials);
    const actionResponse = await fetchEndpoint(publicKeyCredential, actionUrl, actionHeader);
    if (!actionResponse.ok) {
      throw actionResponse;
    }
    const responseBody = await actionResponse.text();

    return responseBody !== "" ? JSON.parse(responseBody) : responseBody;
  };
};

export default useLogin;
