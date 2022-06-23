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

const NotifyHome = React.lazy(() => import("./notify/pages/Home"));
const NotifyComposer = React.lazy(() => import("./notify/forms/Composer"));
const NotifySuccess = React.lazy(() => import("./notify/forms/Composer"));
// const GalasDefaultPage = React.lazy(() => import("./galas/forms/GalasDefaultPage"));
// const GalaHomePage = React.lazy(() => import("./galas/forms/GalaHome"));
const AboutReactApp = React.lazy(() => import("./pages/AboutReactApp"));
const JuniorLeagueMembers = React.lazy(() => import("./admin/forms/JuniorLeagueMembers"));
const WebAuthn = React.lazy(() => import("./my-account/webauthn/WebAuthn"));

const rootElement = document.getElementById("root");
render(
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
            <Route path="my-account/passkeys" element={<WebAuthn />} />
            <Route
              path="*"
              element={<NotFound />}
            />
          </Route>
        </Routes>
      </Suspense>
    </BrowserRouter>
  </Provider>,
  rootElement
);
