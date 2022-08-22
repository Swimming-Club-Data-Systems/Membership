/**
 * Form component
 */

import React, { useContext, useEffect } from "react";
import {
    Formik,
    Form as FormikForm,
    useFormikContext,
    ErrorMessage,
} from "formik";
import { usePage } from "@inertiajs/inertia-react";
import Button from "../Button";
import Alert, { AlertList } from "../Alert";
import { Inertia } from "@inertiajs/inertia";

export const FormSpecialContext = React.createContext({});

export const SubmissionButtons = (props) => {
    const { isSubmitting, dirty, isValid, errors, handleReset } =
        useFormikContext();

    const formSpecialContext = useContext(FormSpecialContext);

    const clearForm = () => {
        if (props.onClear) {
            props.onClear();
        }
        handleReset();
    };

    return (
        <>
            {false && errors && (
                <p className="text-end text-danger">
                    There are{" "}
                    <strong>{Object.keys(errors).length} errors</strong>
                </p>
            )}
            <div className="text-right">
                {!formSpecialContext.hideClear && (
                    <>
                        <Button
                            type="button"
                            onClick={clearForm}
                            disabled={isSubmitting || !dirty}
                            variant="secondary"
                        >
                            {formSpecialContext.clearTitle || "Clear"}
                        </Button>{" "}
                    </>
                )}

                <Button
                    className={formSpecialContext.submitClass}
                    type="submit"
                    disabled={!dirty || !isValid || isSubmitting}
                >
                    {formSpecialContext.submitTitle || "Submit"}
                </Button>
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
            errorList.push(<li key={key}>{value}</li>);
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

const Form = (props) => {
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
        hideDefaultButtons = false,
        removeDefaultInputMargin,
        action,
        method = "post",
        formName,
        inertiaOptions = {},
        ...otherProps
    } = props;

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
                onSuccess: (arg) => formikBag.resetForm(),
                ...inertiaOptions
            });
        }
    };

    const newInitialValues = usePage().props?.form_initial_values;
    const mergedValues = { ...initialValues, ...newInitialValues };

    return (
        <FormSpecialContext.Provider
            value={{
                hideClear: hideClear,
                clearTitle: clearTitle,
                submitTitle: submitTitle,
                submitClass: submitClass,
                removeDefaultInputMargin: removeDefaultInputMargin,
                formName: formName,
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

                    {props.children}

                    {!hideDefaultButtons && (
                        <SubmissionButtons
                            submitTitle={submitTitle}
                            hideClear={hideClear}
                            clearTitle={clearTitle}
                            onClear={onClear}
                        />
                    )}
                </FormikForm>
            </Formik>
        </FormSpecialContext.Provider>
    );
};

export default Form;
