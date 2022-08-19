import React from "react";
import { Navigate, useLocation } from "react-router-dom";
import SuspenseFallback from "./SuspenseFallback";

export function PublicNotFound() {

  let location = useLocation();

  return (
    <>
      <Navigate to="/login" replace state={{ location: location }} />
      <SuspenseFallback />
    </>
  );
}
