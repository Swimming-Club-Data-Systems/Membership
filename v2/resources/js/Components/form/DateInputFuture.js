import React, { useState, useRef } from "react";
import { useField, useFormikContext } from "formik";
import { Form } from "react-bootstrap";
import { DayPicker, useInput } from "react-day-picker";
import "react-day-picker/dist/style.css";
import "./DateInput.css";
import { usePopper } from "react-popper";
import FocusTrap from "focus-trap-react";
import { format, isValid, parse } from "date-fns";

const DateInput = ({ label, help, mb, disabled, ...props }) => {
  const { setFieldValue } = useFormikContext();
  const [field, meta] = useField(props);
  const [selected, setSelected] = useState();
  const [isPopperOpen, setIsPopperOpen] = useState(false);
  const { isSubmitting } = useFormikContext();
  const marginBotton = mb || "mb-3";
  const propMerge = {
    //...inputProps,
    ...props,
  };

  const popperRef = useRef(null);
  const inputRef = useRef(null);
  const [popperElement, setPopperElement] = useState(null);

  const popper = usePopper(popperRef.current, popperElement, {
    placement: "bottom-start",
  });

  const closePopper = () => {
    setIsPopperOpen(false);
    inputRef?.current?.focus();
  };

  const handleInputChange = (e) => {
    const { value } = e.currentTarget;
    setInputValue(value);
    const date = parse(value, "y-MM-dd", new Date());
    if (isValid(date)) {
      setSelected(date);
    } else {
      setSelected(undefined);
    }
  };

  const handleButtonClick = () => {
    inputRef.current.focus();
    setIsPopperOpen(true);
  };

  const handleDaySelect = (date) => {
    setSelected(date);
    if (date) {
      setInputValue(format(date, "y-MM-dd"));
      closePopper();
    } else {
      setInputValue("");
    }
  };

  const setInputValue = (value) => {
    setFieldValue(props.name, value);
  };

  const onChange = (ev) => {
    if (field.onChange) {
      field.onChange(ev);
    }
    handleInputChange(ev);
  };

  return (
    <>
      <div ref={popperRef}>
        <Form.Group className={marginBotton} controlId={props.id || props.name}>
          <Form.Label>{label}</Form.Label>
          <Form.Control
            // type="date"
            // onChange={handleInputChange}
            isValid={props.showValid && meta.touched && !meta.error}
            isInvalid={meta.touched && meta.error}
            disabled={isSubmitting || disabled}
            onFocus={handleButtonClick}
            onClick={handleButtonClick}
            ref={inputRef}
            {...field}
            {...propMerge}
            onChange={onChange}
            // value={}
          />

          {meta.touched && meta.error ? (
            <Form.Control.Feedback type="invalid">
              {meta.error}
            </Form.Control.Feedback>
          ) : null}

          {help && <Form.Text className="text-muted">{help}</Form.Text>}
        </Form.Group>

        {isPopperOpen && (
          <FocusTrap
            active
            focusTrapOptions={{
              initialFocus: false,
              allowOutsideClick: true,
              clickOutsideDeactivates: true,
              onDeactivate: closePopper,
            }}
          >
            <div
              tabIndex={-1}
              style={popper.styles.popper}
              className="dialog-sheet bg-light"
              {...popper.attributes.popper}
              ref={setPopperElement}
              role="dialog"
            >
              <DayPicker
                initialFocus={isPopperOpen}
                mode="single"
                defaultMonth={selected}
                selected={selected}
                onSelect={handleDaySelect}
              />
            </div>
          </FocusTrap>
        )}
      </div>
    </>
  );
};

export default DateInput;
