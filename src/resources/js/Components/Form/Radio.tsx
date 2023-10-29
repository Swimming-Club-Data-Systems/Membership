import React, { useContext } from "react";
import RadioCheck, { RadioCheckProps } from "./RadioCheck";
import { RadioGroupContext } from "@/Components/Form/RadioGroup";

interface Props extends Omit<RadioCheckProps, "name" | "type" | "inContext"> {
    name?: string;
    value: string | number;
}

const Radio = ({ name, ...props }: Props) => {
    const { name: contextName } = useContext(RadioGroupContext);

    return (
        <RadioCheck
            name={contextName || name}
            {...props}
            type="radio"
            inContext={!!contextName}
        />
    );
};

export default Radio;
