import * as OnboardingIds from "../membership-and-onboarding/ids/Onboarding.ids";

export const mapStateToProps = (state) => {
  const main = state.MainStore;
  const props = {
    ...main,
    [OnboardingIds.FORM_TO_DISPLAY]: main[OnboardingIds.FORM_TO_DISPLAY] || OnboardingIds.SELECT_TYPE,
  };

  return props;
};