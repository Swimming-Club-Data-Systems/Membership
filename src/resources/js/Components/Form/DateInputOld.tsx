import React, { useContext, useEffect, useMemo, useRef, useState } from "react";
import "react-day-picker/dist/style.css";
import "./DateInput.css";
import BaseInput from "@/Components/Form/base/BaseInput";
import { useField, useFormikContext } from "formik";
import { parse } from "date-fns";
import { DayPicker } from "react-day-picker";
import { FormSpecialContext } from "@/Components/Form/Form";
import FocusTrap from "focus-trap-react";
import { usePopper } from "react-popper";
import {
    checkISOFormatAndLength,
    findMatchedFormatAndValue,
    formattedValue,
    isDateValid,
    parseISODate,
    formatToISO,
    additionalYears,
    parseDate,
    getSeparator,
} from "./DateInputUtils";
import { enGB } from "date-fns/locale";
import getFormatData from "@/Components/Form/DateInputFormats";
import useClickAwayListener from "@/Components/Form/base/useClickAwayListener";

export const DateInputOld = ({ className = "", ...props }) => {
    const { setFieldValue } = useFormikContext();
    const [field, meta] = useField(props);
    const [displayValue, setDisplayValue] = useState();

    useEffect(() => {
        // Set the display value
    }, []);

    const [selected, setSelected] = useState();
    const [isPopperOpen, setIsPopperOpen] = useState(false);
    const { isSubmitting } = useFormikContext();
    const isBlurBlocked = useRef(false);
    const propMerge = {
        //...inputProps,
        ...props,
    };
    const { formName, readOnly, ...context } = useContext(FormSpecialContext);

    const { format, formats } = useMemo(() => getFormatData(enGB.code), [enGB]);

    const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error && meta.error.length > 0;

    const controlId =
        (formName ? formName + "_" : "") + (props.id || props.name);

    const popperRef = useRef(null);
    const inputRef = useRef(null);
    const [popperElement, setPopperElement] = useState(null);

    const popper = usePopper(popperRef.current, popperElement, {
        placement: "bottom-start",
    });

    const [selectedDays, setSelectedDays] = useState(
        checkISOFormatAndLength(props.value)
            ? parseISODate(props.value)
            : parseDate(format, props.value)
    );

    let errorClasses = "";
    if (isInvalid) {
        errorClasses =
            "pr-10 border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500";
    }

    if (!props.leftText) {
        className += " rounded-l-md ";
    }

    if (!props.rightButton) {
        className += " rounded-r-md ";
    }

    // bottom-start

    const closePopper = () => {
        setIsPopperOpen(false);
        inputRef?.current?.focus();
    };

    const handleInputChange = (e) => {
        const { value } = e.target;
        setFieldValue(props.name, value.rawValue);
        setDisplayValue(value.formattedValue);
        const date = parse(value.rawValue, "y-MM-dd", new Date());
        if (isDateValid(date)) {
            setSelected(date);
        } else {
            setSelected(undefined);
        }
    };

    const handleClickAway = () => {
        if (open) {
            // alreadyFocused.current = true;
            inputRef.current.focus();
            isBlurBlocked.current = false;
            inputRef.current.blur();
            setIsPopperOpen(false);
            // alreadyFocused.current = false;
        }
    };

    const handleClickInside = useClickAwayListener(
        handleClickAway,
        "mousedown"
    );

    const handleButtonClick = () => {
        inputRef.current.focus();
        setIsPopperOpen(true);
    };

    const handleKeyDown = (ev) => {
        if (props.onKeyDown) {
            props.onKeyDown(ev);
        }

        if (ev.code === "Tab") {
            setIsPopperOpen(false);
            // alreadyFocused.current = false;
        }
    };

    const computeInvalidRawValue = (inputValue) =>
        props.allowEmptyValue && !inputValue.length ? inputValue : null;

    const buildCustomEvent = (ev) => {
        const { id, name } = ev.target;

        const [matchedFormat, matchedValue] = findMatchedFormatAndValue(
            ev.target.value,
            formats
        );

        const formattedValueString =
            ev.type === "blur"
                ? formattedValue(format, selectedDays)
                : ev.target.value;
        const rawValue = isDateValid(parseDate(matchedFormat, matchedValue))
            ? formatToISO(...additionalYears(matchedFormat, matchedValue))
            : computeInvalidRawValue(ev.target.value);

        ev.target = {
            ...(name && { name }),
            ...(id && { id }),
            value: {
                formattedValue: formattedValueString,
                rawValue,
            },
        };

        return ev;
    };

    const handleMouseDown = (ev) => {
        handleClickInside(ev);

        if (props.disabled || readOnly) {
            return;
        }

        if (setInputRefMap) {
            isBlurBlocked.current = true;
        }

        if (ev.target.type === "text" && !open) {
            setIsPopperOpen(true);
        } else if (ev.target.type !== "text") {
            // alreadyFocused.current = true;
            setIsPopperOpen((prev) => !prev);
        }
    };

    const handlePickerMouseDown = (ev) => {
        isBlurBlocked.current = true;
        handleClickInside(ev);
    };

    // const assignInput = (input) => {
    //     inputRef.current = input.current;
    //     parentRef.current = input.current.parentElement;
    //
    //     if (inputRefMap && inputRefMap[name]?.setOpen !== setIsPopperOpen()) {
    //         setInputRefMap({
    //             [name]: { isBlurBlocked, setIsPopperOpen },
    //         });
    //     }
    // };

    useEffect(() => {
        const [matchedFormat, matchedValue] = findMatchedFormatAndValue(
            field.value,
            formats
        );

        if (
            matchedFormat &&
            matchedValue &&
            isDateValid(parseDate(matchedFormat, matchedValue))
        ) {
            setSelectedDays(
                parseDate(...additionalYears(matchedFormat, matchedValue))
            );
        } else if (
            checkISOFormatAndLength(field.value) &&
            isInitialValue.current
        ) {
            setSelectedDays(parseISODate(field.value));
        } else {
            setSelectedDays(undefined);
        }
    }, [field.value, formats]);

    const handleBlur = (ev) => {
        if (props.disabled || readOnly) {
            return;
        }

        let event;

        if (isDateValid(selectedDays)) {
            event = buildCustomEvent(ev);

            const currentValue = checkISOFormatAndLength(field.value)
                ? formattedValue(format, parseISODate(field.value))
                : field.value;
            const [, matchedValue] = findMatchedFormatAndValue(
                currentValue,
                formats
            );

            if (formattedValue(format, selectedDays) !== matchedValue) {
                onChange(event);
            }
        } else {
            const { id, name } = ev.target;

            ev.target = {
                ...(name && { name }),
                ...(id && { id }),
                value: {
                    formattedValue: ev.target.value,
                    rawValue: computeInvalidRawValue(ev.target.value),
                },
            };

            event = ev;
        }

        if (isBlurBlocked.current) {
            return;
        }

        if (props.onBlur) {
            props.onBlur(event);
        }
    };

    const computedValue = () => {
        const value = field.value;

        if (checkISOFormatAndLength(value) && !meta.touched) {
            return formattedValue(format, parseISODate(value));
        }

        const valueSeparator = getSeparator(field.value);
        const formatSeparator = getSeparator(format);
        const replaceSeparators = () =>
            value
                .split("")
                .map((char) =>
                    char === valueSeparator ? formatSeparator : char
                )
                .join("");

        if (
            //!meta.touched &&
            valueSeparator !== formatSeparator &&
            isDateValid(parseDate(format, replaceSeparators()))
        ) {
            // isInitialValue.current = false;

            const [matchedFormat, matchedValue] = findMatchedFormatAndValue(
                replaceSeparators(),
                formats
            );
            return formattedValue(
                format,
                parseDate(...additionalYears(matchedFormat, matchedValue))
            );
        }

        console.log(value, format);

        return value;
    };

    // const handleDaySelect = (date) => {
    //     setSelected(date);
    //     if (date) {
    //         setInputValue(format(date, "y-MM-dd"));
    //         closePopper();
    //     } else {
    //         setInputValue("");
    //     }
    // };

    const onChange = (ev) => {
        if (field.onChange) {
            field.onChange(ev);
        }
        handleInputChange(ev);
    };

    const handleOnDayClickParent = (day, ev) => {
        setSelectedDays(day);
        onChange(
            buildCustomEvent({
                ...ev,
                target: {
                    ...ev.target,
                    value: formattedValue(format, day),
                },
            })
        );
        inputRef.current.focus();
        setIsPopperOpen(false);
    };

    const handleDayClick = (date, { disabled }, ev) => {
        if (!disabled) {
            const { id, name } = popperRef?.current?.firstChild; // setPopperElement
            ev.target = {
                ...ev.target,
                id,
                name,
            };
            handleOnDayClickParent(date, ev);
        }
    };

    return (
        <div className="relative mt-1">
            <div ref={popperRef}>
                <BaseInput
                    {...props}
                    input={
                        <input
                            onFocus={handleButtonClick}
                            onClick={handleButtonClick}
                            onBlur={handleBlur}
                            ref={inputRef}
                            {...field}
                            value={computedValue()}
                            {...propMerge}
                            onChange={onChange}
                            isInvalid={isInvalid}
                            isValid={isValid}
                            error={meta.error}
                            readOnly={props.readOnly}
                            disabled={
                                isSubmitting ||
                                props.disabled ||
                                context.disabled
                            }
                            className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
                            id={controlId}
                            type="text"
                            {...props}
                            onKeyDown={handleKeyDown}
                        />
                    }
                />
            </div>
            {isPopperOpen && (
                <FocusTrap
                    active
                    focusTrapOptions={{
                        initialFocus: false,
                        allowOutsideClick: true,
                        clickOutsideDeactivates: true,
                        onDeactivate: closePopper,
                        fallbackFocus: inputRef.current,
                    }}
                >
                    <div
                        tabIndex={-1}
                        style={popper.styles.popper}
                        className="dialog-sheet absolute z-10 mt-1 overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                        {...popper.attributes.popper}
                        ref={setPopperElement}
                        role="dialog"
                    >
                        <DayPicker
                            initialFocus={isPopperOpen}
                            mode="single"
                            defaultMonth={selected}
                            selected={selected}
                            // onSelect={handleDaySelect}
                            fixedWeeks
                            onDayClick={handleDayClick}
                        />
                    </div>
                </FocusTrap>
            )}
        </div>
    );
};

export default DateInputOld;
