import React from "react";
import { connect } from "formik";
import TextInput from "../../components/form/TextInput";
import DateInput from "../../components/form/DateInput";
import Checkbox from "../../components/form/Checkbox";

const OnboardingStages = (props) => {

  return (
    <>
      <h2>Onboarding details</h2>
      <TextInput
        label="Member/Parent name"
        name="memberParentName"
        readOnly
      />

      <TextInput
        label="Onboarding session ID"
        name="onboardingId"
        readOnly
      />

      <TextInput
        label="Membership batch ID"
        name="batchId"
        readOnly
      />

      <TextInput
        label="Onboarding creator name"
        name="onboardingCreatorName"
        readOnly
      />

      <DateInput
        label="Member start date"
        name="startDate"
      />

      <Checkbox
        label="Has due date"
        name="hasDueDate"
      />

      <DateInput
        label="Onboarding due date"
        name="dueDate"
        disabled={!props.formik.values.hasDueDate}
      />

      <Checkbox
        label="Charge fees up to first direct debit date"
        name="chargeFees"
      />

      <Checkbox
        label="Charge pro-rata amount between start date and first direct debit date"
        name="proRata"
      />
    </>
  );

};

export default connect(OnboardingStages);