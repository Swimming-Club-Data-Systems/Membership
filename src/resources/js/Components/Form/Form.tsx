/**
 * Form component
 */

import React, { ReactNode, useContext, useEffect, useState } from "react";
import {
    Form as FormikForm,
    Formik,
    FormikBag,
    useFormikContext,
} from "formik";
import { usePage, router, VisitOptions } from "@inertiajs/react";
import Button from "../Button";
import Alert, { AlertList } from "../Alert";
import { merge } from "lodash";
import { AnyObjectSchema } from "yup";

interface FormSpecialContextInterface {
    submitClass?: string;
    submitTitle?: string;
    hideClear?: boolean;
    alwaysClearable?: boolean;
    formName?: string;
    clearTitle?: string;
    removeDefaultInputMargin?: boolean;
    alwaysDirty?: boolean;
    disabled?: boolean;
    readOnly?: boolean;
    hasErrors?: boolean;
}

export const FormSpecialContext =
    React.createContext<FormSpecialContextInterface | null>({});

type SubmissionButtonsProps = {
    onClear?: () => void;
};

export const SubmissionButtons: React.FC<SubmissionButtonsProps> = (props) => {
    const { isSubmitting, dirty, errors, handleReset, touched } =
        useFormikContext();

    const formSpecialContext = useContext(FormSpecialContext);

    const clearForm = () => {
        if (props.onClear) {
            props.onClear();
        }
        handleReset();
    };

    const numErrors = Object.keys(errors).reduce((total, current) => {
        if (errors[current].length > 0 && touched[current]) {
            total += 1;
        }
        return total;
    }, 0);

    return (
        <div className="flex gap-4 items-center justify-between">
            <div>
                {numErrors > 0 && (
                    <div className="text-red-500 text-sm">
                        {/* May reinstate icon later */}
                        {/*<ExclamationCircleIcon*/}
                        {/*    className="h-5 w-5 text-red-500 inline"*/}
                        {/*    aria-hidden="true"*/}
                        {/*/>{" "}*/}
                        <>
                            {numErrors === 1 && (
                                <>
                                    <span className="sr-only">There is </span>
                                    <strong>1</strong> error to correct
                                </>
                            )}
                            {numErrors !== 1 && (
                                <>
                                    <span className="sr-only">There are </span>
                                    <strong>{numErrors}</strong> errors to
                                    correct
                                </>
                            )}
                        </>
                    </div>
                )}
            </div>
            <div className="flex flex-row-reverse gap-4">
                <Button
                    className={`inline-flex justify-center ${formSpecialContext.submitClass}`}
                    type="submit"
                    disabled={isSubmitting}
                >
                    {formSpecialContext.submitTitle || "Submit"}
                </Button>

                {!formSpecialContext.hideClear && (
                    <>
                        <Button
                            type="button"
                            onClick={clearForm}
                            disabled={
                                isSubmitting ||
                                (!dirty && !formSpecialContext.alwaysClearable)
                            }
                            variant="secondary"
                            className="inline-flex justify-center"
                        >
                            {formSpecialContext.clearTitle || "Clear"}
                        </Button>{" "}
                    </>
                )}
            </div>
        </div>
    );
};

export const UnknownError = () => {
    const { hasErrors } = useContext(FormSpecialContext);

    if (hasErrors) {
        return (
            <Alert
                className="mb-3"
                variant="error"
                title="An unknown error occurred"
                // handleDismiss={handleNetErrorDismiss}
            >
                Please check your form data and try again.
            </Alert>
        );
    }
    return null;
};

const HandleServerErrors = () => {
    const formSpecialContext = useContext(FormSpecialContext);

    // If we're scoped, get those errors, otherwise just the errors
    const errors = formSpecialContext.formName
        ? usePage().props?.errors[formSpecialContext.formName]
        : usePage().props.errors;

    const { setStatus, setSubmitting } = useFormikContext();

    useEffect(() => {
        if (errors) {
            setStatus(errors);
            setSubmitting(false);
        }
    }, [errors]);

    return null;
};

export const RenderServerErrors = () => {
    const errors = useFormikContext().status;

    if (errors) {
        const errorList = [];
        Object.entries(errors).forEach(([key, value]) => {
            if (typeof value === "string") {
                errorList.push(<li key={key}>{value}</li>);
            }
        });

        if (errorList.length > 0) {
            return (
                <Alert
                    className="mb-3"
                    variant="error"
                    title="There are errors to correct"
                >
                    <AlertList>{errorList}</AlertList>
                </Alert>
            );
        }
    }

    return null;
};

type FormProps = {
    initialValues?: Record<string, unknown>;
    validationSchema: AnyObjectSchema | (() => AnyObjectSchema);
    onSubmit?: (
        values: Record<string, unknown>,
        formikBag: FormikBag<never, never>
    ) => void;
    submitTitle?: string;
    submitClass?: string;
    hideClear?: boolean;
    hideErrors?: boolean;
    clearTitle?: string;
    onClear?: () => void;
    alwaysClearable?: boolean;
    hideDefaultButtons?: boolean;
    removeDefaultInputMargin?: boolean;
    action?: string;
    method?: string;
    formName?: string;
    inertiaOptions?: VisitOptions;
    alwaysDirty?: boolean;
    children?: ReactNode;
    disabled?: boolean;
    readOnly?: boolean;
};

const Form = (props: FormProps) => {
    const {
        initialValues,
        validationSchema,
        onSubmit,
        submitTitle,
        submitClass,
        hideClear,
        hideErrors,
        clearTitle,
        onClear,
        alwaysClearable = false,
        hideDefaultButtons = false,
        removeDefaultInputMargin,
        action,
        method = "post",
        formName = null,
        inertiaOptions = {},
        alwaysDirty = false,
        disabled = false,
        readOnly = false,
        ...otherProps
    } = props;

    const [hasErrors, setHasErrors] = useState(false);
    const handleNetErrorDismiss = () => {
        setHasErrors(false);
    };

    useEffect(() => {
        // If we get an invalid response, don't show any of the response
        // Instead show a warning to the user in the form
        return router.on("invalid", (event) => {
            setHasErrors(true);
            // When in production, show the dev error messages
            if (import.meta.env.PROD) {
                event.preventDefault();
            }
        });
    }, []);

    const onSubmitHandler = (values, formikBag) => {
        formikBag.setStatus({});
        if (onSubmit) {
            // Escape hatch override
            onSubmit(values, formikBag);
        } else {
            // Use default behaviour
            if (formName) {
                inertiaOptions.errorBag = formName;
            }

            router[method](action, values, {
                onSuccess: () => formikBag.resetForm(),
                ...inertiaOptions,
                // onError: (error) => {
                //     console.log(error);
                // },
                onFinish: () => {
                    // Always mark as not submitting once done
                    formikBag.setSubmitting(false);
                },
            });
        }
    };

    type InitialValues = Record<string, unknown> | null;
    const pageProps: Record<string, unknown> = usePage().props;

    const newInitialValues: InitialValues = formName
        ? // eslint-disable-next-line @typescript-eslint/ban-ts-comment
          // @ts-ignore
          pageProps[formName]?.form_initial_values
        : pageProps.form_initial_values;
    const mergedValues = merge(initialValues, newInitialValues);

    return (
        <FormSpecialContext.Provider
            value={{
                hideClear: hideClear,
                clearTitle: clearTitle,
                submitTitle: submitTitle,
                submitClass: submitClass,
                removeDefaultInputMargin: removeDefaultInputMargin,
                formName: formName,
                alwaysDirty: alwaysDirty,
                alwaysClearable: alwaysClearable,
                disabled: disabled,
                readOnly: readOnly,
                hasErrors: hasErrors,
            }}
        >
            <Formik
                initialValues={mergedValues}
                validationSchema={validationSchema}
                onSubmit={onSubmitHandler}
                enableReinitialize
            >
                <FormikForm {...otherProps}>
                    <HandleServerErrors />

                    {!hideErrors && <RenderServerErrors />}

                    {!hideErrors && <UnknownError />}

                    {props.children}

                    {!hideDefaultButtons && (
                        <div className="mt-5 sm:mt-4">
                            <SubmissionButtons onClear={onClear} />
                        </div>
                    )}
                </FormikForm>
            </Formik>
        </FormSpecialContext.Provider>
    );
};

export default Form;
