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
import { usePage } from "@inertiajs/inertia-react";
import Button from "../Button";
import Alert, { AlertList } from "../Alert";
import { Inertia, VisitOptions } from "@inertiajs/inertia";
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
}

export const FormSpecialContext =
    React.createContext<FormSpecialContextInterface | null>({});

type SubmissionButtonsProps = {
    onClear?: () => void;
};

export const SubmissionButtons: React.FC<SubmissionButtonsProps> = (props) => {
    const { isSubmitting, dirty, errors, handleReset } = useFormikContext();

    const formSpecialContext = useContext(FormSpecialContext);

    const clearForm = () => {
        if (props.onClear) {
            props.onClear();
        }
        handleReset();
    };

    return (
        <>
            {/*{errors && (*/}
            {/*    <p className="text-end text-danger">*/}
            {/*        There are{" "}*/}
            {/*        <strong>{Object.keys(errors).length} errors</strong>*/}
            {/*    </p>*/}
            {/*)}*/}
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
        </>
    );
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
    initialValues: Record<string, unknown>;
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
        ...otherProps
    } = props;

    const [hasErrors, setHasErrors] = useState(false);
    const handleNetErrorDismiss = () => {
        setHasErrors(false);
    };

    useEffect(() => {
        // If we get an invalid response, don't show any of the response
        // Instead show a warning to the user in the form
        return Inertia.on("invalid", (event) => {
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

            Inertia[method](action, values, {
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

                    {hasErrors && (
                        <Alert
                            className="mb-3"
                            variant="error"
                            title="An unknown error occurred"
                            handleDismiss={handleNetErrorDismiss}
                        >
                            Please check your form data and try again.
                        </Alert>
                    )}

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
