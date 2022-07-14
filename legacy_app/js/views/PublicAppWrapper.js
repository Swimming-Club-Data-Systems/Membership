/**
 * AppWrapper for header and footer
 */

import React, { useState, useEffect } from "react";
import axios from "axios";
import store from "../reducers/store";
import SuspenseFallback from "./SuspenseFallback";
import { Outlet } from "react-router-dom";
import Footer from "../components/Footer";

const PublicAppWrapper = () => {

  const [hasTenantInfo, setHasTenantInfo] = useState(false);

  useEffect(
    () => {
      axios.get("/api/settings/tenant")
        .then(response => {
          let data = response.data;

          store.dispatch({
            type: "ADD_TENANT_KEYS",
            payload: data.keys,
          });

          store.dispatch({
            type: "ADD_TENANT_DETAILS",
            payload: data.tenant,
          });

          setHasTenantInfo(true);
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    []
  );

  return (

    <>
      {
        hasTenantInfo
          ? <>
            <Outlet />
            <Footer />
          </>
          :
          <SuspenseFallback />
      }
    </>

  );
};

export default PublicAppWrapper;