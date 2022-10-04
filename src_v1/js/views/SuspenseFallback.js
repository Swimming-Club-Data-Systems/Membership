/**
 * Suspense fallback
 */

import React from "react";
// import Placeholder from 'react-bootstrap/Placeholder';
import { Placeholder } from "react-bootstrap";
import Header from "../components/Header";

const SuspenseFallback = () => {

  const title = (
    <Placeholder xs={4} />
  );

  const subtitle = (
    <Placeholder xs={7} />
  );

  return (

    <Placeholder animation="glow">

      <Header title={title} subtitle={subtitle} />

      <div className="container-xl">
        <div className="row">
          <div className="col-lg-8">
            <p aria-hidden="true" className="lead">
              <Placeholder xs={4} /> <Placeholder xs={5} /> <Placeholder xs={3} />{" "}
              <Placeholder xs={3} /> <Placeholder xs={4} /> <Placeholder xs={2} />{" "}
              <Placeholder xs={3} /> <Placeholder xs={1} /> <Placeholder xs={4} />{" "}
              <Placeholder xs={4} />
            </p>

            <p aria-hidden="true">
              <Placeholder xs={5} /> <Placeholder xs={2} /> <Placeholder xs={3} />{" "}
              <Placeholder xs={4} /> <Placeholder xs={3} /> <Placeholder xs={1} />{" "}
              <Placeholder xs={3} /> <Placeholder xs={3} /> <Placeholder xs={2} />{" "}
              <Placeholder xs={1} />
            </p>

            <p aria-hidden="true">
              <Placeholder xs={7} /> <Placeholder xs={4} /> <Placeholder xs={4} />{" "}
              <Placeholder xs={6} /> <Placeholder xs={8} />
            </p>

            <p aria-hidden="true">
              <Placeholder xs={5} /> <Placeholder xs={3} /> <Placeholder xs={7} />{" "}
              <Placeholder xs={4} /> <Placeholder xs={6} />
            </p>
          </div>
        </div>
      </div>

    </Placeholder>
  );
};

export default SuspenseFallback;