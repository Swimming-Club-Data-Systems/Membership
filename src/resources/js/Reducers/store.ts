import { configureStore, createSlice } from "@reduxjs/toolkit";

const mainSlice = createSlice({
    name: "main",
    initialState: {},
    reducers: {
        setKey: (state, action) => {
            state[action.payload.key] = action.payload.value;
        },
        setKeys: (state, action) => {
            action.payload.forEach((item) => {
                state[item[0]] = item[1];
            });
        },
    },
});

const apiCountSlice = createSlice({
    name: "apiCount",
    initialState: {
        value: 0,
    },
    reducers: {
        increment: (state) => {
            state.value += 1;
        },
        decrement: (state) => {
            state.value -= 1;
        },
    },
});

const turnstileSlice = createSlice({
    name: "showCloudflareTurnstile",
    initialState: {
        value: false,
    },
    reducers: {
        showTurnstile: (state) => {
            state.value = true;
        },
        hideTurnstile: (state) => {
            state.value = false;
        },
    },
});

export const { setKey, setKeys } = mainSlice.actions;
export const { increment, decrement } = apiCountSlice.actions;
export const { showTurnstile, hideTurnstile } = turnstileSlice.actions;

export const apiCount = (state) => state.apiCount.value;

export const store = configureStore({
    reducer: {
        main: mainSlice.reducer,
        apiCount: apiCountSlice.reducer,
        showCloudflareTurnstile: turnstileSlice.reducer,
    },
});

// Infer the `RootState` and `AppDispatch` types from the store itself
export type RootState = ReturnType<typeof store.getState>;
// Inferred type: {posts: PostsState, comments: CommentsState, users: UsersState}
export type AppDispatch = typeof store.dispatch;
