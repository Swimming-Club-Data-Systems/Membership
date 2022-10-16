import React from "react";
import InternalContainer from "./InternalContainer";

const Card = (props) => {
    const { className = "" } = props;

    return (
        <div className={`bg-white shadow sm:rounded-lg ${className}`}>
            {props.header && (
                <div className="py-3 bg-gray-50 text-right sm:rounded-t-lg">
                    <InternalContainer>{props.header}</InternalContainer>
                </div>
            )}
            <div className="py-6">
                <InternalContainer>
                    <div className="space-y-6">{props.children}</div>
                </InternalContainer>
            </div>
            {props.footer && (
                <div className="py-3 bg-gray-50 text-right sm:rounded-b-lg">
                    <InternalContainer>{props.footer}</InternalContainer>
                </div>
            )}
        </div>
    );
};

export default Card;
