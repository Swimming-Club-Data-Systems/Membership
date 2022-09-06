import React from "react";
import { decrement, increment, store } from "@/Reducers/store";
import axios from "axios";

/**
 * Set defaults on axios to power things like disabling buttons
 */

// Add a request interceptor
axios.interceptors.request.use(
    function (config) {
        // Tell our global state that a request has started
        store.dispatch(increment());
        return config;
    },
    function (error) {
        // Do something with request error
        return Promise.reject(error);
    }
);

// Add a response interceptor
axios.interceptors.response.use(
    function (response) {
        // Any status code that lie within the range of 2xx cause this function to trigger
        // Do something with response data
        // Tell our global state that a request has ended
        store.dispatch(decrement());
        return response;
    },
    function (error) {
        // Any status codes that falls outside the range of 2xx cause this function to trigger
        // Do something with response error
        // Tell our global state that a request has ended
        store.dispatch(decrement());
        return Promise.reject(error);
    }
);

export default axios;
