/**
 * AppWrapper for header and footer
 */

import React, { useState, useEffect } from "react";
import axios from "axios";
import store from "../reducers/store";
import SuspenseFallback from "./SuspenseFallback";
import { Outlet } from "react-router-dom";

const PublicAppFooter = () => {

  const [header, setHeader] = useState(null);
  const [footer, setFooter] = useState(null);
  const [hasTenantInfo, setHasTenantInfo] = useState(false);

  useEffect(
    () => {
      axios.get("/api/react/header-footer")
        .then(response => {
          let data = response.data;
          setHeader(data.header);
          setFooter(data.footer);
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    []
  );

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
        hasTenantInfo && footer
          ? <>
            <div className="have-full-height focus-highlight">
              <Outlet />
            </div>
            <div dangerouslySetInnerHTML={{ __html: footer }} />
          </>
          :
          <SuspenseFallback />
      }
    </>

  );
};

export default PublicAppFooter;