import React from "react";
import {useField} from "formik";

const Hidden = (props) => {
    const [field, meta] = useField(props);
    const controlId = props.id || props.name;

    return <>
        <input
            type="hidden"
            id={controlId}
            {...field}
            {...props}
        />
    </>
}

export default Hidden;
