import Container from "@/Components/Container";
import React from "react";

const MainHeader = (props) => {
    let buttons = null;

    if (props.buttons) {
        buttons = (
            <div className="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-reverse sm:space-y-0 sm:space-x-3 md:mt-0 md:flex-row md:space-x-3">
                {props.buttons}
            </div>
        );
    }

    return (
        <Container>
            <div className="pb-10 md:flex md:items-center md:justify-between md:space-x-5">
                <div className="flex items-center space-x-5">
                    {/* Optional image section */}
                    {/* <div className="flex-shrink-0">
                    <div className="relative">
                        <img
                            className="h-16 w-16 rounded-full"
                            src="https://images.unsplash.com/photo-1463453091185-61582044d556?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=1024&h=1024&q=80"
                            alt=""
                        />
                        <span
                            className="absolute inset-0 shadow-inner rounded-full"
                            aria-hidden="true"
                        />
                    </div>
                </div> */}
                    <div>
                        {props.title && (
                            <h1 className="text-2xl font-bold text-gray-900">
                                {props.title}
                            </h1>
                        )}
                        {props.subtitle && (
                            <p className="text-sm font-medium text-gray-500">
                                {props.subtitle}
                            </p>
                        )}
                    </div>
                </div>
                {buttons}
            </div>
        </Container>
    );
};

export default MainHeader;
