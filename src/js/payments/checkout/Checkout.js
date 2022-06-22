import React, { useContext, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { loadStripe } from "@stripe/stripe-js";
import {
  PaymentElement,
  Elements,
  useStripe,
  useElements,
  PaymentRequestButtonElement,
} from "@stripe/react-stripe-js";
import * as tenantFunctions from "../../classes/Tenant";
import axios from "axios";
import Form, { SubmissionButtons } from "../../components/form/Form";
import { Alert, Button } from "react-bootstrap";
import Select from "../../components/form/Select";
import ItemList from "./ItemList";
import * as Financials from "../../classes/Financials";

const stripePromise = loadStripe(tenantFunctions.getStripe(), {
  stripeAccount: tenantFunctions.getStripeAccountId()
});

export const PaymentContext = React.createContext(null);

const CheckoutForm = (props) => {

  const data = useContext(PaymentContext);

  const stripe = useStripe();
  const elements = useElements();

  const [errorMessage, setErrorMessage] = useState(null);
  const [loading, setLoading] = React.useState(false);
  const [paymentRequest, setPaymentRequest] = useState(null);

  useEffect(() => {
    (async () => {
      if (!stripe) return;
      const { paymentIntent } = await stripe.retrievePaymentIntent(data.client_secret);
      if (paymentIntent.status === "succeeded") {
        props.onSuccess();
      }
    })();
  }, [stripe]);

  useEffect(() => {
    if (stripe) {
      const pr = stripe.paymentRequest({
        country: "GB",
        currency: data.currency,
        displayItems: data.payment_request_items,
        total: {
          label: data.org_name,
          amount: data.amount,
        },
        requestPayerName: true,
        requestPayerEmail: true,
      });

      // Check the availability of the Payment Request API.
      pr.canMakePayment().then(result => {
        if (result) {
          setPaymentRequest(pr);
        }
      });
    }
  }, [stripe]);

  useEffect(() => {
    if (!paymentRequest) return;

    paymentRequest.on("paymentmethod", async (ev) => {
      setErrorMessage(null);

      // Confirm the PaymentIntent without handling potential next actions (yet).
      const { paymentIntent, error: confirmError } = await stripe.confirmCardPayment(
        data.client_secret,
        { payment_method: ev.paymentMethod.id },
        { handleActions: false }
      );

      if (confirmError) {
        // Report to the browser that the payment failed, prompting it to
        // re-show the payment interface, or show an error message and close
        // the payment interface.
        ev.complete("fail");
        setErrorMessage(confirmError.message);
      } else {
        // Report to the browser that the confirmation was successful, prompting
        // it to close the browser payment method collection interface.
        ev.complete("success");
        // Check if the PaymentIntent requires any actions and if so let Stripe.js
        // handle the flow. If using an API version older than "2019-02-11"
        // instead check for: `paymentIntent.status === "requires_source_action"`.
        if (paymentIntent.status === "requires_action") {
          // Let Stripe.js handle the rest of the payment flow.
          const { error } = await stripe.confirmCardPayment(data.clientSecret);
          if (error) {
            // The payment failed -- ask your customer for a new payment method.
            setErrorMessage(error.message);
            if (props.onError) {
              props.onError(error);
            }
          } else {
            // The payment has succeeded.
            // Onwards
            if (props.onSuccess) {
              props.onSuccess();
            }
          }
        } else {
          // The payment has succeeded.
          if (props.onSuccess) {
            props.onSuccess();
          }
        }
      }
    });
  }, [paymentRequest]);

  const handleSubmit = async (event) => {

    // console.log(event, stripe, elements);

    // We don't want to let default form submission happen here,
    // which would refresh the page.
    if (!stripe || !elements) {
      // Stripe.js has not yet loaded.
      // Make sure to disable form submission until Stripe.js has loaded.
      return;
    }

    setLoading(true);

    if (props.handleSubmit) {
      const result = await props.handleSubmit(event);
      if (!result) {
        setLoading(false);
        return;
      }
    }

    console.log(event);

    const result = await stripe.confirmPayment({
      //`Elements` instance that was used to create the Payment Element
      elements,
      redirect: "if_required",
      confirmParams: {
        return_url: data.redirect_url,
      },
    });

    setLoading(false);

    if (result.error) {
      // This point will only be reached if there is an immediate error when
      // confirming the payment. Show error to your customer (for example, payment
      // details incomplete)
      setErrorMessage(result.error.message);
      if (props.onError) {
        props.onError(result.error);
      }
    } else {
      // Your customer will be redirected to your `return_url`. For some payment
      // methods like iDEAL, your customer will be redirected to an intermediate
      // site first to authorize the payment, then redirected to the `return_url`.
      setErrorMessage(result.paymentIntent.status);
      if (props.onSuccess) {
        props.onSuccess();
      }
    }
  };

  // useEffect(() => {
  //   // Return handleSubmit to form higher up
  //   props.setHandleSubmit(handleSubmit);
  // }, []);

  // console.log(stripe, elements);

  return (
    <>
      <Form
        onSubmit={handleSubmit}
        hideButtons
        initialValues={{
          existingMethod: "select",
        }}
      >
        {
          paymentRequest &&
          <>
            <div className="mb-3">
              <PaymentRequestButtonElement options={{ paymentRequest }} />
            </div>
            <p className="text-center">Or</p>
          </>
        }

        {data.payment_methods.length > 0 &&
          <>
            <div className="card card-body mb-3">
              <Select size="lg" name="existingMethod" label="Select a saved payment method" options={data.payment_methods.map((item) => {
                return { value: item.id, name: `${item.type_data.brand} ${item.type_data.description}` };
              })} />

              {errorMessage &&
                <div className="alert alert-danger mb-3">
                  <p className="mb-0">
                    <strong>Oops</strong>
                  </p>
                  <p className="mb-0">
                    {errorMessage}
                  </p>
                </div>
              }

              <div className="d-grid">
                <Button variant="primary" size="lg" type="submit" disabled={!stripe || loading}>
                  {loading ? "Processing..." : "Pay now"}
                </Button>
              </div>
            </div>

            <p className="text-center">Or</p>
          </>
        }

        <div className="card card-body mb-3">
          <div className="mb-3">
            <PaymentElement options={{
              business: {
                name: data.org_name,
              },
              wallets: {
                applePay: "never",
                googlePay: "never",
              }
            }} />
          </div>

          {errorMessage &&
            <div className="alert alert-danger mb-3">
              <p className="mb-0">
                <strong>Oops</strong>
              </p>
              <p className="mb-0">
                {errorMessage}
              </p>
            </div>
          }

          <div className="d-grid">
            <Button variant="primary" size="lg" type="submit" disabled={!stripe || loading}>
              {loading ? "Processing..." : "Pay now"}
            </Button>
          </div>
        </div>
      </Form>
    </>
  );
};

const Checkout = (props) => {
  const { id } = props;
  const [data, setData] = useState(null);

  useEffect(() => {
    // Get the checkout session info
    (async () => {
      const response = await axios.get("/api/payments/checkout", {
        params: {
          id: id,
        }
      });

      setData(response.data);
    })();
  }, []);

  if (!data) {
    return <>Loading</>;
  }

  const options = {
    clientSecret: data.client_secret,
    appearance: {
      theme: "stripe",
      variables: {
        colorPrimary: "#0570de",
        colorBackground: "#ffffff",
        colorText: "#30313d",
        colorDanger: "#df1b41",
        fontFamily: "-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Oxygen, Ubuntu, Cantarell, \"Open Sans\", \"Helvetica Neue\", sans-serif",
        spacingUnit: "5px",
        borderRadius: "5px",
        // See all possible variables below
      }
    },
  };

  return (
    <PaymentContext.Provider value={data}>
      <Elements stripe={stripePromise} options={options}>
        {props.children}
      </Elements>
    </PaymentContext.Provider>
  );
};

const AboutPayment = () => {
  const data = useContext(PaymentContext);

  return (
    <>
      <h1 className="mb-5">
        <small className="text-muted h4">Pay {tenantFunctions.getName()}{data.test_mode ? " (Test Mode)" : null}</small> <br />{Financials.formatCurrency(Financials.intToDec(data.amount), data.currency)}
      </h1>

      <div className="mb-5">
        <ItemList />
      </div>

      <p className="text-muted">
        Provided by Swimming Club Data Systems and its service partners.
      </p>
    </>
  );

};

const CheckoutPage = () => {
  let { id } = useParams();

  const [success, setSuccess] = useState(false);

  const onSuccess = () => setSuccess(true);

  return (
    <div className="container py-5">
      {!success &&
        <Checkout
          id={id}
        >
          <div className="row justify-content-around">
            <div className="col-lg-5">
              <AboutPayment />
            </div>
            <div className="col-lg-5">
              <CheckoutForm onSuccess={onSuccess} />
            </div>
          </div>
        </Checkout>
      }
      {
        success &&
        <div className="container">
          <Alert variant="success">
            <p className="mb-0">
              <strong>Success</strong>
            </p>
            <p className="mb-0">
              Your payment has been successful
            </p>
          </Alert>
        </div>
      }
    </div >
  );
};

export default CheckoutPage;