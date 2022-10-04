import axios from "axios";

export const isPwned = async (password) => {
  try {
    const result = await axios.post("/v1/api/utilities/pwned-password-check", {
      password: password,
    });

    return !result.data.pwned;
  } catch (error) {
    return true;
  }
};