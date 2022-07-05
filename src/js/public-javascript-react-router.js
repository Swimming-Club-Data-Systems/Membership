import React, { Suspense } from "react";
import { render } from "react-dom";
import { Provider } from "react-redux";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import SuspenseFallback from "./views/SuspenseFallback";
import ScrollToTop from "./components/global/ScrollToTop";
import store from "./reducers/store";
import PublicAppWrapper from "./views/PublicAppWrapper";
import { PublicNotFound } from "./views/PublicNotFound";
import NotFound from "./views/NotFound";
import PublicAppFooter from "./views/PublicAppFooter";
import { GlobalErrorBoundary } from "./views/GlobalErrorBoundary";
import PublicAppPage from "./views/PublicAppPage";

const Welcome = React.lazy(() => import("./pages/public/Welcome"));
const LoginPageWrapper = React.lazy(() => import("./login/LoginPageWrapper"));
const LoginPage = React.lazy(() => import("./login/LoginPage"));
const FindAccount = React.lazy(() => import("./login/FindAccount"));
const ResetPassword = React.lazy(() => import("./login/ResetPassword"));
const AboutReactApp = React.lazy(() => import("./pages/AboutReactApp"));
const CheckoutPage = React.lazy(() => import("./payments/checkout/Checkout"));

const rootElement = document.getElementById("root");
render(
  <GlobalErrorBoundary>
    <Provider store={store}>
      <BrowserRouter>
        <ScrollToTop />
        <Suspense fallback={<SuspenseFallback />}>
          <Routes>
            <Route path="/" element={<PublicAppWrapper />}>
              <Route path="/login">
                <Route element={<LoginPageWrapper />}>
                  <Route index element={<LoginPage />} />
                  <Route path="forgot-password" element={<FindAccount />} />
                  <Route path="reset-password" element={<ResetPassword />} />
                </Route>
              </Route>
              <Route path="/" element={<PublicAppPage />}>
                <Route index element={<Welcome />} />
                <Route path="about" element={<AboutReactApp />} />
                <Route path="timetable" element={<NotFound redirect={true} />} />
                <Route path="timeconverter" element={<NotFound redirect={true} />} />
                <Route path="log-books" element={<NotFound redirect={true} />} />
                <Route path="404" element={<NotFound />} />
                <Route path="payments/checkout/v2/:id" element={<CheckoutPage />} />
              </Route>
            </Route>
            <Route
              path="*"
              element={<PublicNotFound />}
            />
          </Routes>
        </Suspense>
      </BrowserRouter>
    </Provider>
  </GlobalErrorBoundary>,
  rootElement
);
