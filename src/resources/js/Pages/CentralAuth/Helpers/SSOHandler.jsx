import React, { useEffect } from "react";
import axios from "axios";
import { useField } from "formik";

const SSOHandler = ({ setSsoUrl }) => {
    // Use form context to make requests and get user info

    const [{ value }, meta, helpers] = useField("email");

    useEffect(async () => {
        console.log("Calling ", value, meta);
        if (!meta.error) {
            try {
                const { data } = await axios.post(route("login.check_user"), {
                    email: value,
                });

                setSsoUrl(data.sso_url);

                // If url, set touched so form can be submitted
                if (data.sso_url) {
                    helpers.setTouched(true, true);
                }
            } catch (error) {
                setSsoUrl(null);
            }
        } else {
            setSsoUrl(null);
        }
    }, [value]);

    return null;
};

export default SSOHandler;
