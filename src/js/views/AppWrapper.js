/**
 * AppWrapper for header and footer
 */

import React, { useState, useEffect } from "react";
import axios from "axios";
import store from "../reducers/store";
import SuspenseFallback from "../views/SuspenseFallback";
import { Outlet } from "react-router-dom";

const AppWrapper = () => {

  const [hasTenantInfo, setHasTenantInfo] = useState(false);
  const [hasUserInfo, setHasUserInfo] = useState(false);

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

  useEffect(
    () => {
      axios.get("/api/settings/user")
        .then(response => {
          let data = response.data;

          store.dispatch({
            type: "ADD_USER_KEYS",
            payload: data.keys,
          });

          store.dispatch({
            type: "ADD_USER_DETAILS",
            payload: data.user,
          });

          setHasUserInfo(true);
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
        hasTenantInfo && hasUserInfo
          ? <>
            <Outlet />
          </>
          :
          <SuspenseFallback />
      }
    </>

  );
};

export default AppWrapper;