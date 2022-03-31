import React, { Suspense } from "react";
import { render } from "react-dom";
import { Provider } from "react-redux";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import SuspenseFallback from "./views/SuspenseFallback";
import ScrollToTop from "./components/global/ScrollToTop";
import store from "./reducers/store";
import AppWrapper from "./views/AppWrapper";
import NotFound from "./views/NotFound";
import IsAuthorised from "./components/IsAuthorised";
import { GlobalErrorBoundary } from "./views/GlobalErrorBoundary";

const NotifyHome = React.lazy(() => import("./notify/pages/Home"));
const NotifyComposer = React.lazy(() => import("./notify/forms/Composer"));
const NotifySuccess = React.lazy(() => import("./notify/forms/Composer"));
// const GalasDefaultPage = React.lazy(() => import("./galas/forms/GalasDefaultPage"));
// const GalaHomePage = React.lazy(() => import("./galas/forms/GalaHome"));
const AboutReactApp = React.lazy(() => import("./pages/AboutReactApp"));
const JuniorLeagueMembers = React.lazy(() => import("./admin/forms/JuniorLeagueMembers"));

// Onboarding pages
const OnboardingHome = React.lazy(() => import("./membership-and-onboarding/pages/OnboardingHome"));
const OnboardingWizard = React.lazy(() => import("./membership-and-onboarding/forms/OnboardingWizard"));
const AmendOnboardingSessionForm = React.lazy(() => import("./membership-and-onboarding/forms/AmendOnboardingSessionForm"));

const rootElement = document.getElementById("root");
render(
  <GlobalErrorBoundary>
    <Provider store={store}>
      <BrowserRouter>
        <ScrollToTop />
        <Suspense fallback={<SuspenseFallback />}>
          <Routes>
            <Route path="/" element={<AppWrapper />}>
              <Route path="suspense" element={<SuspenseFallback />} />
              <Route path="notify" element={<NotifyHome />} />
              <Route path="notify/new" element={<NotifyComposer />} />
              <Route path="notify/new/success" element={<NotifySuccess />} />
              <Route path="about" element={<AboutReactApp />} />
              <Route path="admin/reports/junior-league-report" element={<IsAuthorised permissions={["Admin"]}><JuniorLeagueMembers /></IsAuthorised>} />
              <Route path="/memberships">
                {/* <Route index element={<LoginPage />} />
              <Route path="forgot-password" element={<FindAccount />} /> */}
              </Route>
              <Route path="/onboarding">
                <Route index element={<OnboardingHome />} />
                <Route path="new" element={<OnboardingWizard />} />
                <Route path=":sessionId/edit" element={<AmendOnboardingSessionForm />} />
              </Route>
              <Route
                path="*"
                element={<NotFound />}
              />
            </Route>
          </Routes>
        </Suspense>
      </BrowserRouter>
    </Provider>
  </GlobalErrorBoundary>,
  rootElement
);
