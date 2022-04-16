"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_Pages_Welcome_js"],{

/***/ "./resources/js/Components/ApplicationLogo.js":
/*!****************************************************!*\
  !*** ./resources/js/Components/ApplicationLogo.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ApplicationLogo)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");


function ApplicationLogo(_ref) {
  var className = _ref.className;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("img", {
    src: "/img/corporate/scds.png",
    width: "50px",
    className: "rounded"
  });
}

/***/ }),

/***/ "./resources/js/Components/Dropdown.js":
/*!*********************************************!*\
  !*** ./resources/js/Components/Dropdown.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var _inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @inertiajs/inertia-react */ "./node_modules/@inertiajs/inertia-react/dist/index.js");
/* harmony import */ var _headlessui_react__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @headlessui/react */ "./node_modules/@headlessui/react/dist/headlessui.esm.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }







var DropDownContext = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createContext();

var Dropdown = function Dropdown(_ref) {
  var children = _ref.children;

  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
      _useState2 = _slicedToArray(_useState, 2),
      open = _useState2[0],
      setOpen = _useState2[1];

  var toggleOpen = function toggleOpen() {
    setOpen(function (previousState) {
      return !previousState;
    });
  };

  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(DropDownContext.Provider, {
    value: {
      open: open,
      setOpen: setOpen,
      toggleOpen: toggleOpen
    },
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      className: "relative",
      children: children
    })
  });
};

var Trigger = function Trigger(_ref2) {
  var children = _ref2.children;

  var _useContext = (0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(DropDownContext),
      open = _useContext.open,
      setOpen = _useContext.setOpen,
      toggleOpen = _useContext.toggleOpen;

  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      onClick: toggleOpen,
      children: children
    }), open && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      className: "fixed inset-0 z-40",
      onClick: function onClick() {
        return setOpen(false);
      }
    })]
  });
};

var Content = function Content(_ref3) {
  var _ref3$align = _ref3.align,
      align = _ref3$align === void 0 ? 'right' : _ref3$align,
      _ref3$width = _ref3.width,
      width = _ref3$width === void 0 ? '48' : _ref3$width,
      _ref3$contentClasses = _ref3.contentClasses,
      contentClasses = _ref3$contentClasses === void 0 ? 'py-1 bg-white' : _ref3$contentClasses,
      children = _ref3.children;

  var _useContext2 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(DropDownContext),
      open = _useContext2.open,
      setOpen = _useContext2.setOpen;

  var alignmentClasses = 'origin-top';

  if (align === 'left') {
    alignmentClasses = 'origin-top-left left-0';
  } else if (align === 'right') {
    alignmentClasses = 'origin-top-right right-0';
  }

  var widthClasses = '';

  if (width === '48') {
    widthClasses = 'w-48';
  }

  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.Fragment, {
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_headlessui_react__WEBPACK_IMPORTED_MODULE_3__.Transition, {
      show: open,
      enter: "transition ease-out duration-200",
      enterFrom: "transform opacity-0 scale-95",
      enterTo: "transform opacity-100 scale-100",
      leave: "transition ease-in duration-75",
      leaveFrom: "transform opacity-100 scale-100",
      leaveTo: "transform opacity-0 scale-95",
      children: open && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
        className: "absolute z-50 mt-2 rounded-md shadow-lg ".concat(alignmentClasses, " ").concat(widthClasses),
        onClick: function onClick() {
          return setOpen(false);
        },
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
          className: "rounded-md ring-1 ring-black ring-opacity-5 " + contentClasses,
          children: children
        })
      })
    })
  });
};

var DropdownLink = function DropdownLink(_ref4) {
  var href = _ref4.href,
      _ref4$method = _ref4.method,
      method = _ref4$method === void 0 ? 'post' : _ref4$method,
      _ref4$as = _ref4.as,
      as = _ref4$as === void 0 ? 'a' : _ref4$as,
      children = _ref4.children;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_1__.Link, {
    href: href,
    method: method,
    as: as,
    className: "block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out",
    children: children
  });
};

Dropdown.Trigger = Trigger;
Dropdown.Content = Content;
Dropdown.Link = DropdownLink;
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Dropdown);

/***/ }),

/***/ "./resources/js/Components/NavLink.js":
/*!********************************************!*\
  !*** ./resources/js/Components/NavLink.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ NavLink)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var _inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @inertiajs/inertia-react */ "./node_modules/@inertiajs/inertia-react/dist/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");
var _excluded = ["href", "active", "children"];

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }




function NavLink(_ref) {
  var href = _ref.href,
      active = _ref.active,
      children = _ref.children,
      otherProps = _objectWithoutProperties(_ref, _excluded);

  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_1__.Link, _objectSpread(_objectSpread({
    href: href,
    className: active ? 'flex p-2 rounded bg-gray-100 w-full mb-2' : 'flex p-2 rounded hover:bg-indigo-100 focus:bg-indigo-200 transition duration-150 ease-in-out w-full mb-2'
  }, otherProps), {}, {
    children: children
  }));
}

/***/ }),

/***/ "./resources/js/Components/ResponsiveNavLink.js":
/*!******************************************************!*\
  !*** ./resources/js/Components/ResponsiveNavLink.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ResponsiveNavLink)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var _inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @inertiajs/inertia-react */ "./node_modules/@inertiajs/inertia-react/dist/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");



function ResponsiveNavLink(_ref) {
  var _ref$method = _ref.method,
      method = _ref$method === void 0 ? 'get' : _ref$method,
      _ref$as = _ref.as,
      as = _ref$as === void 0 ? 'a' : _ref$as,
      href = _ref.href,
      _ref$active = _ref.active,
      active = _ref$active === void 0 ? false : _ref$active,
      children = _ref.children;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_1__.Link, {
    method: method,
    as: as,
    href: href,
    className: "w-full flex items-start pl-3 pr-4 py-2 border-l-4 ".concat(active ? 'border-indigo-400 text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300', " text-base font-medium focus:outline-none transition duration-150 ease-in-out"),
    children: children
  });
}

/***/ }),

/***/ "./resources/js/Layouts/Authenticated.js":
/*!***********************************************!*\
  !*** ./resources/js/Layouts/Authenticated.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Authenticated)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var _Components_ApplicationLogo__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/Components/ApplicationLogo */ "./resources/js/Components/ApplicationLogo.js");
/* harmony import */ var _Components_Dropdown__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/Components/Dropdown */ "./resources/js/Components/Dropdown.js");
/* harmony import */ var _Components_NavLink__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/Components/NavLink */ "./resources/js/Components/NavLink.js");
/* harmony import */ var _Components_ResponsiveNavLink__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/Components/ResponsiveNavLink */ "./resources/js/Components/ResponsiveNavLink.js");
/* harmony import */ var _inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @inertiajs/inertia-react */ "./node_modules/@inertiajs/inertia-react/dist/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }









function Authenticated(_ref) {
  var auth = _ref.auth,
      header = _ref.header,
      children = _ref.children;

  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
      _useState2 = _slicedToArray(_useState, 2),
      showingNavigationDropdown = _useState2[0],
      setShowingNavigationDropdown = _useState2[1];

  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
    className: "flex",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
      className: "basis-64 h-screen p-3",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
        className: "mb-3",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_5__.Link, {
          href: "/",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_Components_ApplicationLogo__WEBPACK_IMPORTED_MODULE_1__["default"], {
            className: "block h-9 w-auto text-gray-500"
          })
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("hr", {}), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
          className: "py-3",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_Components_NavLink__WEBPACK_IMPORTED_MODULE_3__["default"], {
            href: route('home'),
            active: route().current('home'),
            children: "Home"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_Components_NavLink__WEBPACK_IMPORTED_MODULE_3__["default"], {
            href: route('dashboard'),
            active: route().current('dashboard'),
            children: "Dashboard"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_Components_NavLink__WEBPACK_IMPORTED_MODULE_3__["default"], {
            href: route('login'),
            children: "Log In"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_Components_NavLink__WEBPACK_IMPORTED_MODULE_3__["default"], {
            href: route('register'),
            children: "Register"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_Components_NavLink__WEBPACK_IMPORTED_MODULE_3__["default"], {
            href: route('logout'),
            method: "post",
            as: "button",
            children: "Log Out"
          })]
        })
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
      className: "grow",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
        className: "min-h-screen bg-gray-100",
        children: [header && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("header", {
          className: "bg-white shadow",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
            className: "max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8",
            children: header
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("main", {
          children: children
        })]
      })
    })]
  });
}

/***/ }),

/***/ "./resources/js/Pages/Welcome.js":
/*!***************************************!*\
  !*** ./resources/js/Pages/Welcome.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Welcome)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var _Layouts_Authenticated__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/Layouts/Authenticated */ "./resources/js/Layouts/Authenticated.js");
/* harmony import */ var _inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @inertiajs/inertia-react */ "./node_modules/@inertiajs/inertia-react/dist/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");






function Welcome(props) {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(_Layouts_Authenticated__WEBPACK_IMPORTED_MODULE_1__["default"], {
    auth: props.auth,
    errors: props.errors,
    header: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("h2", {
      className: "font-semibold text-xl text-gray-800 leading-tight",
      children: "Welcome"
    }),
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_2__.Head, {
      title: "Welcome"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
      className: "relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
        className: "fixed top-0 right-0 px-6 py-4 sm:block",
        children: props.auth.user ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_2__.Link, {
          href: route('dashboard'),
          className: "text-sm text-gray-700 underline",
          children: "Dashboard"
        }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.Fragment, {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_2__.Link, {
            href: route('login'),
            className: "text-sm text-gray-700 underline",
            children: "Log in"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_inertiajs_inertia_react__WEBPACK_IMPORTED_MODULE_2__.Link, {
            href: route('register'),
            className: "ml-4 text-sm text-gray-700 underline",
            children: "Register"
          })]
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
        className: "max-w-6xl mx-auto sm:px-6 lg:px-8",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
          className: "flex justify-center pt-8 sm:justify-start sm:pt-0",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("svg", {
            viewBox: "0 0 651 192",
            fill: "none",
            xmlns: "http://www.w3.org/2000/svg",
            className: "h-16 w-auto text-gray-700 sm:h-20",
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("g", {
              clipPath: "url(#clip0)",
              fill: "#EF3B2D",
              children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("path", {
                d: "M248.032 44.676h-16.466v100.23h47.394v-14.748h-30.928V44.676zM337.091 87.202c-2.101-3.341-5.083-5.965-8.949-7.875-3.865-1.909-7.756-2.864-11.669-2.864-5.062 0-9.69.931-13.89 2.792-4.201 1.861-7.804 4.417-10.811 7.661-3.007 3.246-5.347 6.993-7.016 11.239-1.672 4.249-2.506 8.713-2.506 13.389 0 4.774.834 9.26 2.506 13.459 1.669 4.202 4.009 7.925 7.016 11.169 3.007 3.246 6.609 5.799 10.811 7.66 4.199 1.861 8.828 2.792 13.89 2.792 3.913 0 7.804-.955 11.669-2.863 3.866-1.908 6.849-4.533 8.949-7.875v9.021h15.607V78.182h-15.607v9.02zm-1.431 32.503c-.955 2.578-2.291 4.821-4.009 6.73-1.719 1.91-3.795 3.437-6.229 4.582-2.435 1.146-5.133 1.718-8.091 1.718-2.96 0-5.633-.572-8.019-1.718-2.387-1.146-4.438-2.672-6.156-4.582-1.719-1.909-3.032-4.152-3.938-6.73-.909-2.577-1.36-5.298-1.36-8.161 0-2.864.451-5.585 1.36-8.162.905-2.577 2.219-4.819 3.938-6.729 1.718-1.908 3.77-3.437 6.156-4.582 2.386-1.146 5.059-1.718 8.019-1.718 2.958 0 5.656.572 8.091 1.718 2.434 1.146 4.51 2.674 6.229 4.582 1.718 1.91 3.054 4.152 4.009 6.729.953 2.577 1.432 5.298 1.432 8.162-.001 2.863-.479 5.584-1.432 8.161zM463.954 87.202c-2.101-3.341-5.083-5.965-8.949-7.875-3.865-1.909-7.756-2.864-11.669-2.864-5.062 0-9.69.931-13.89 2.792-4.201 1.861-7.804 4.417-10.811 7.661-3.007 3.246-5.347 6.993-7.016 11.239-1.672 4.249-2.506 8.713-2.506 13.389 0 4.774.834 9.26 2.506 13.459 1.669 4.202 4.009 7.925 7.016 11.169 3.007 3.246 6.609 5.799 10.811 7.66 4.199 1.861 8.828 2.792 13.89 2.792 3.913 0 7.804-.955 11.669-2.863 3.866-1.908 6.849-4.533 8.949-7.875v9.021h15.607V78.182h-15.607v9.02zm-1.432 32.503c-.955 2.578-2.291 4.821-4.009 6.73-1.719 1.91-3.795 3.437-6.229 4.582-2.435 1.146-5.133 1.718-8.091 1.718-2.96 0-5.633-.572-8.019-1.718-2.387-1.146-4.438-2.672-6.156-4.582-1.719-1.909-3.032-4.152-3.938-6.73-.909-2.577-1.36-5.298-1.36-8.161 0-2.864.451-5.585 1.36-8.162.905-2.577 2.219-4.819 3.938-6.729 1.718-1.908 3.77-3.437 6.156-4.582 2.386-1.146 5.059-1.718 8.019-1.718 2.958 0 5.656.572 8.091 1.718 2.434 1.146 4.51 2.674 6.229 4.582 1.718 1.91 3.054 4.152 4.009 6.729.953 2.577 1.432 5.298 1.432 8.162 0 2.863-.479 5.584-1.432 8.161zM650.772 44.676h-15.606v100.23h15.606V44.676zM365.013 144.906h15.607V93.538h26.776V78.182h-42.383v66.724zM542.133 78.182l-19.616 51.096-19.616-51.096h-15.808l25.617 66.724h19.614l25.617-66.724h-15.808zM591.98 76.466c-19.112 0-34.239 15.706-34.239 35.079 0 21.416 14.641 35.079 36.239 35.079 12.088 0 19.806-4.622 29.234-14.688l-10.544-8.158c-.006.008-7.958 10.449-19.832 10.449-13.802 0-19.612-11.127-19.612-16.884h51.777c2.72-22.043-11.772-40.877-33.023-40.877zm-18.713 29.28c.12-1.284 1.917-16.884 18.589-16.884 16.671 0 18.697 15.598 18.813 16.884h-37.402zM184.068 43.892c-.024-.088-.073-.165-.104-.25-.058-.157-.108-.316-.191-.46-.056-.097-.137-.176-.203-.265-.087-.117-.161-.242-.265-.345-.085-.086-.194-.148-.29-.223-.109-.085-.206-.182-.327-.252l-.002-.001-.002-.002-35.648-20.524a2.971 2.971 0 00-2.964 0l-35.647 20.522-.002.002-.002.001c-.121.07-.219.167-.327.252-.096.075-.205.138-.29.223-.103.103-.178.228-.265.345-.066.089-.147.169-.203.265-.083.144-.133.304-.191.46-.031.085-.08.162-.104.25-.067.249-.103.51-.103.776v38.979l-29.706 17.103V24.493a3 3 0 00-.103-.776c-.024-.088-.073-.165-.104-.25-.058-.157-.108-.316-.191-.46-.056-.097-.137-.176-.203-.265-.087-.117-.161-.242-.265-.345-.085-.086-.194-.148-.29-.223-.109-.085-.206-.182-.327-.252l-.002-.001-.002-.002L40.098 1.396a2.971 2.971 0 00-2.964 0L1.487 21.919l-.002.002-.002.001c-.121.07-.219.167-.327.252-.096.075-.205.138-.29.223-.103.103-.178.228-.265.345-.066.089-.147.169-.203.265-.083.144-.133.304-.191.46-.031.085-.08.162-.104.25-.067.249-.103.51-.103.776v122.09c0 1.063.568 2.044 1.489 2.575l71.293 41.045c.156.089.324.143.49.202.078.028.15.074.23.095a2.98 2.98 0 001.524 0c.069-.018.132-.059.2-.083.176-.061.354-.119.519-.214l71.293-41.045a2.971 2.971 0 001.489-2.575v-38.979l34.158-19.666a2.971 2.971 0 001.489-2.575V44.666a3.075 3.075 0 00-.106-.774zM74.255 143.167l-29.648-16.779 31.136-17.926.001-.001 34.164-19.669 29.674 17.084-21.772 12.428-43.555 24.863zm68.329-76.259v33.841l-12.475-7.182-17.231-9.92V49.806l12.475 7.182 17.231 9.92zm2.97-39.335l29.693 17.095-29.693 17.095-29.693-17.095 29.693-17.095zM54.06 114.089l-12.475 7.182V46.733l17.231-9.92 12.475-7.182v74.537l-17.231 9.921zM38.614 7.398l29.693 17.095-29.693 17.095L8.921 24.493 38.614 7.398zM5.938 29.632l12.475 7.182 17.231 9.92v79.676l.001.005-.001.006c0 .114.032.221.045.333.017.146.021.294.059.434l.002.007c.032.117.094.222.14.334.051.124.088.255.156.371a.036.036 0 00.004.009c.061.105.149.191.222.288.081.105.149.22.244.314l.008.01c.084.083.19.142.284.215.106.083.202.178.32.247l.013.005.011.008 34.139 19.321v34.175L5.939 144.867V29.632h-.001zm136.646 115.235l-65.352 37.625V148.31l48.399-27.628 16.953-9.677v33.862zm35.646-61.22l-29.706 17.102V66.908l17.231-9.92 12.475-7.182v33.841z"
              })
            })
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
          className: "mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
            className: "grid grid-cols-1 md:grid-cols-2",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
              className: "p-6",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
                className: "flex items-center",
                children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("svg", {
                  fill: "none",
                  stroke: "currentColor",
                  strokeLinecap: "round",
                  strokeLinejoin: "round",
                  strokeWidth: "2",
                  viewBox: "0 0 24 24",
                  className: "w-8 h-8 text-gray-500",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("path", {
                    d: "M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                  })
                }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                  className: "ml-4 text-lg leading-7 font-semibold",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://laravel.com/docs",
                    className: "underline text-gray-900 dark:text-white",
                    children: "Documentation"
                  })
                })]
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                className: "ml-12",
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                  className: "mt-2 text-gray-600 dark:text-gray-400 text-sm",
                  children: "Laravel has wonderful, thorough documentation covering every aspect of the framework. Whether you are new to the framework or have previous experience with Laravel, we recommend reading all of the documentation from beginning to end."
                })
              })]
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
              className: "p-6 border-t border-gray-200 dark:border-gray-700 md:border-t-0 md:border-l",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
                className: "flex items-center",
                children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("svg", {
                  fill: "none",
                  stroke: "currentColor",
                  strokeLinecap: "round",
                  strokeLinejoin: "round",
                  strokeWidth: "2",
                  viewBox: "0 0 24 24",
                  className: "w-8 h-8 text-gray-500",
                  children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("path", {
                    d: "M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                  }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("path", {
                    d: "M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                  })]
                }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                  className: "ml-4 text-lg leading-7 font-semibold",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://laracasts.com",
                    className: "underline text-gray-900 dark:text-white",
                    children: "Laracasts"
                  })
                })]
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                className: "ml-12",
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                  className: "mt-2 text-gray-600 dark:text-gray-400 text-sm",
                  children: "Laracasts offers thousands of video tutorials on Laravel, PHP, and JavaScript development. Check them out, see for yourself, and massively level up your development skills in the process."
                })
              })]
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
              className: "p-6 border-t border-gray-200 dark:border-gray-700",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
                className: "flex items-center",
                children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("svg", {
                  fill: "none",
                  stroke: "currentColor",
                  strokeLinecap: "round",
                  strokeLinejoin: "round",
                  strokeWidth: "2",
                  viewBox: "0 0 24 24",
                  className: "w-8 h-8 text-gray-500",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("path", {
                    d: "M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                  })
                }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                  className: "ml-4 text-lg leading-7 font-semibold",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://laravel-news.com/",
                    className: "underline text-gray-900 dark:text-white",
                    children: "Laravel News"
                  })
                })]
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                className: "ml-12",
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                  className: "mt-2 text-gray-600 dark:text-gray-400 text-sm",
                  children: "Laravel News is a community driven portal and newsletter aggregating all of the latest and most important news in the Laravel ecosystem, including new package releases and tutorials."
                })
              })]
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
              className: "p-6 border-t border-gray-200 dark:border-gray-700 md:border-l",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
                className: "flex items-center",
                children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("svg", {
                  fill: "none",
                  stroke: "currentColor",
                  strokeLinecap: "round",
                  strokeLinejoin: "round",
                  strokeWidth: "2",
                  viewBox: "0 0 24 24",
                  className: "w-8 h-8 text-gray-500",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("path", {
                    d: "M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  })
                }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                  className: "ml-4 text-lg leading-7 font-semibold text-gray-900 dark:text-white",
                  children: "Vibrant Ecosystem"
                })]
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
                className: "ml-12",
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
                  className: "mt-2 text-gray-600 dark:text-gray-400 text-sm",
                  children: ["Laravel's robust library of first-party tools and libraries, such as", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://forge.laravel.com",
                    className: "underline",
                    children: "Forge"
                  }), ",", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://vapor.laravel.com",
                    className: "underline",
                    children: "Vapor"
                  }), ",", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://nova.laravel.com",
                    className: "underline",
                    children: "Nova"
                  }), ", and", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://envoyer.io",
                    className: "underline",
                    children: "Envoyer"
                  }), ' ', "help you take your projects to the next level. Pair them with powerful open source libraries like", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://laravel.com/docs/billing",
                    className: "underline",
                    children: "Cashier"
                  }), ",", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://laravel.com/docs/dusk",
                    className: "underline",
                    children: "Dusk"
                  }), ",", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://laravel.com/docs/broadcasting",
                    className: "underline",
                    children: "Echo"
                  }), ",", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://laravel.com/docs/horizon",
                    className: "underline",
                    children: "Horizon"
                  }), ",", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://laravel.com/docs/sanctum",
                    className: "underline",
                    children: "Sanctum"
                  }), ",", ' ', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                    href: "https://laravel.com/docs/telescope",
                    className: "underline",
                    children: "Telescope"
                  }), ", and more."]
                })
              })]
            })]
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
          className: "flex justify-center mt-4 sm:items-center sm:justify-between",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
            className: "text-center text-sm text-gray-500 sm:text-left",
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
              className: "flex items-center",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("svg", {
                fill: "none",
                strokeLinecap: "round",
                strokeLinejoin: "round",
                strokeWidth: "2",
                viewBox: "0 0 24 24",
                stroke: "currentColor",
                className: "-mt-px w-5 h-5 text-gray-400",
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("path", {
                  d: "M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                })
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                href: "https://laravel.bigcartel.com",
                className: "ml-1 underline",
                children: "Shop"
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("svg", {
                fill: "none",
                stroke: "currentColor",
                strokeLinecap: "round",
                strokeLinejoin: "round",
                strokeWidth: "2",
                viewBox: "0 0 24 24",
                className: "ml-4 -mt-px w-5 h-5 text-gray-400",
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("path", {
                  d: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                })
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("a", {
                href: "https://github.com/sponsors/taylorotwell",
                className: "ml-1 underline",
                children: "Sponsor"
              })]
            })
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
            className: "ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0",
            children: ["Laravel v", props.laravelVersion, " (PHP v", props.phpVersion, ")"]
          })]
        })]
      })]
    })]
  });
}

/***/ }),

/***/ "./node_modules/@headlessui/react/dist/headlessui.esm.js":
/*!***************************************************************!*\
  !*** ./node_modules/@headlessui/react/dist/headlessui.esm.js ***!
  \***************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Combobox": () => (/* binding */ Na),
/* harmony export */   "Dialog": () => (/* binding */ An),
/* harmony export */   "Disclosure": () => (/* binding */ Ye),
/* harmony export */   "FocusTrap": () => (/* binding */ yu),
/* harmony export */   "Listbox": () => (/* binding */ Ee),
/* harmony export */   "Menu": () => (/* binding */ Ze),
/* harmony export */   "Popover": () => (/* binding */ Te),
/* harmony export */   "Portal": () => (/* binding */ We),
/* harmony export */   "RadioGroup": () => (/* binding */ lt),
/* harmony export */   "Switch": () => (/* binding */ Qt),
/* harmony export */   "Tab": () => (/* binding */ De),
/* harmony export */   "Transition": () => (/* binding */ mt)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-dom */ "./node_modules/react-dom/index.js");
function k(){let e=[],t=[],r={enqueue(o){t.push(o)},requestAnimationFrame(...o){let n=requestAnimationFrame(...o);r.add(()=>cancelAnimationFrame(n))},nextFrame(...o){r.requestAnimationFrame(()=>{r.requestAnimationFrame(...o)})},setTimeout(...o){let n=setTimeout(...o);r.add(()=>clearTimeout(n))},add(o){e.push(o)},dispose(){for(let o of e.splice(0))o()},async workQueue(){for(let o of t.splice(0))await o()}};return r}function Q(){let[e]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(k);return (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>()=>e.dispose(),[e]),e}var x=typeof window!="undefined"?react__WEBPACK_IMPORTED_MODULE_0__.useLayoutEffect:react__WEBPACK_IMPORTED_MODULE_0__.useEffect;var yt={serverHandoffComplete:!1};function q(){let[e,t]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(yt.serverHandoffComplete);return (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{e!==!0&&t(!0)},[e]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{yt.serverHandoffComplete===!1&&(yt.serverHandoffComplete=!0)},[]),e}var or=0;function to(){return++or}function A(){let e=q(),[t,r]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(e?to:null);return x(()=>{t===null&&r(to())},[t]),t!=null?""+t:void 0}function ke(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(e);return (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{t.current=e},[e]),t}function ee(e,t){let[r,o]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(e),n=ke(e);return x(()=>o(n.current),[n,o,...t]),r}function I(...e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(e);return (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{t.current=e},[e]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(r=>{for(let o of t.current)o!=null&&(typeof o=="function"?o(r):o.current=r)},[t])}function S(e,t,...r){if(e in t){let n=t[e];return typeof n=="function"?n(...r):n}let o=new Error(`Tried to handle "${e}" but there is no handler defined. Only defined handlers are: ${Object.keys(t).map(n=>`"${n}"`).join(", ")}.`);throw Error.captureStackTrace&&Error.captureStackTrace(o,S),o}function E({props:e,slot:t,defaultTag:r,features:o,visible:n=!0,name:i}){if(n)return _e(e,t,r,i);let a=o!=null?o:0;if(a&2){let{static:l=!1,...s}=e;if(l)return _e(s,t,r,i)}if(a&1){let{unmount:l=!0,...s}=e;return S(l?0:1,{[0](){return null},[1](){return _e({...s,hidden:!0,style:{display:"none"}},t,r,i)}})}return _e(e,t,r,i)}function _e(e,t={},r,o){let{as:n=r,children:i,refName:a="ref",...l}=gt(e,["unmount","static"]),s=e.ref!==void 0?{[a]:e.ref}:{},u=typeof i=="function"?i(t):i;if(l.className&&typeof l.className=="function"&&(l.className=l.className(t)),n===react__WEBPACK_IMPORTED_MODULE_0__.Fragment&&Object.keys(l).length>0){if(!(0,react__WEBPACK_IMPORTED_MODULE_0__.isValidElement)(u)||Array.isArray(u)&&u.length>1)throw new Error(['Passing props on "Fragment"!',"",`The current component <${o} /> is rendering a "Fragment".`,"However we need to passthrough the following props:",Object.keys(l).map(c=>`  - ${c}`).join(`
`),"","You can apply a few solutions:",['Add an `as="..."` prop, to ensure that we render an actual element instead of a "Fragment".',"Render a single element as the child so that we can forward the props onto that element."].map(c=>`  - ${c}`).join(`
`)].join(`
`));return (0,react__WEBPACK_IMPORTED_MODULE_0__.cloneElement)(u,Object.assign({},fr(mr(gt(l,["ref"])),u.props,["onClick"]),s))}return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(n,Object.assign({},gt(l,["ref"]),n!==react__WEBPACK_IMPORTED_MODULE_0__.Fragment&&s),u)}function fr(e,t,r){let o=Object.assign({},e);for(let n of r)e[n]!==void 0&&t[n]!==void 0&&Object.assign(o,{[n](i){i.defaultPrevented||e[n](i),i.defaultPrevented||t[n](i)}});return o}function D(e){var t;return Object.assign((0,react__WEBPACK_IMPORTED_MODULE_0__.forwardRef)(e),{displayName:(t=e.displayName)!=null?t:e.name})}function mr(e){let t=Object.assign({},e);for(let r in t)t[r]===void 0&&delete t[r];return t}function gt(e,t=[]){let r=Object.assign({},e);for(let o of t)o in r&&delete r[o];return r}function br(e){throw new Error("Unexpected object: "+e)}function ae(e,t){let r=t.resolveItems();if(r.length<=0)return null;let o=t.resolveActiveIndex(),n=o!=null?o:-1,i=(()=>{switch(e.focus){case 0:return r.findIndex(a=>!t.resolveDisabled(a));case 1:{let a=r.slice().reverse().findIndex((l,s,u)=>n!==-1&&u.length-s-1>=n?!1:!t.resolveDisabled(l));return a===-1?a:r.length-1-a}case 2:return r.findIndex((a,l)=>l<=n?!1:!t.resolveDisabled(a));case 3:{let a=r.slice().reverse().findIndex(l=>!t.resolveDisabled(l));return a===-1?a:r.length-1-a}case 4:return r.findIndex(a=>t.resolveId(a)===e.id);case 5:return null;default:br(e)}})();return i===-1?o:i}function G(e){let t=e.parentElement,r=null;for(;t&&!(t instanceof HTMLFieldSetElement);)t instanceof HTMLLegendElement&&(r=t),t=t.parentElement;let o=(t==null?void 0:t.getAttribute("disabled"))==="";return o&&Tr(r)?!1:o}function Tr(e){if(!e)return!1;let t=e.previousElementSibling;for(;t!==null;){if(t instanceof HTMLLegendElement)return!1;t=t.previousElementSibling}return!0}function w(e,t,r){let o=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(t);o.current=t,(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{function n(i){o.current.call(window,i)}return window.addEventListener(e,n,r),()=>window.removeEventListener(e,n,r)},[e,r])}var Pt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Pt.displayName="OpenClosedContext";function _(){return (0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Pt)}function W({value:e,children:t}){return react__WEBPACK_IMPORTED_MODULE_0__.createElement(Pt.Provider,{value:e},t)}function ro(e){var r;if(e.type)return e.type;let t=(r=e.as)!=null?r:"button";if(typeof t=="string"&&t.toLowerCase()==="button")return"button"}function U(e,t){let[r,o]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(()=>ro(e));return x(()=>{o(ro(e))},[e.type,e.as]),x(()=>{r||!t.current||t.current instanceof HTMLButtonElement&&!t.current.hasAttribute("type")&&o("button")},[r,t]),r}function se({container:e,accept:t,walk:r,enabled:o=!0}){let n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(t),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(r);(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{n.current=t,i.current=r},[t,r]),x(()=>{if(!e||!o)return;let a=n.current,l=i.current,s=Object.assign(c=>a(c),{acceptNode:a}),u=document.createTreeWalker(e,NodeFilter.SHOW_ELEMENT,s,!1);for(;u.nextNode();)l(u.currentNode)},[e,o,n,i])}var Ar={[1](e){return e.disabled||e.comboboxState===1?e:{...e,activeOptionIndex:null,comboboxState:1}},[0](e){return e.disabled||e.comboboxState===0?e:{...e,comboboxState:0}},[2](e,t){return e.disabled===t.disabled?e:{...e,disabled:t.disabled}},[3](e,t){if(e.disabled||e.optionsRef.current&&!e.optionsPropsRef.current.static&&e.comboboxState===1)return e;let r=ae(t,{resolveItems:()=>e.options,resolveActiveIndex:()=>e.activeOptionIndex,resolveId:o=>o.id,resolveDisabled:o=>o.dataRef.current.disabled});return e.activeOptionIndex===r?e:{...e,activeOptionIndex:r}},[4]:(e,t)=>{var i;let r=e.activeOptionIndex!==null?e.options[e.activeOptionIndex]:null,o=Array.from((i=e.optionsRef.current)==null?void 0:i.querySelectorAll('[id^="headlessui-combobox-option-"]')).reduce((a,l,s)=>Object.assign(a,{[l.id]:s}),{}),n=[...e.options,{id:t.id,dataRef:t.dataRef}].sort((a,l)=>o[a.id]-o[l.id]);return{...e,options:n,activeOptionIndex:(()=>r===null?null:n.indexOf(r))()}},[5]:(e,t)=>{let r=e.options.slice(),o=e.activeOptionIndex!==null?r[e.activeOptionIndex]:null,n=r.findIndex(i=>i.id===t.id);return n!==-1&&r.splice(n,1),{...e,options:r,activeOptionIndex:(()=>n===e.activeOptionIndex||o===null?null:r.indexOf(o))()}}},vt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);vt.displayName="ComboboxContext";function pe(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(vt);if(t===null){let r=new Error(`<${e} /> is missing a parent <Combobox /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,pe),r}return t}var Rt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Rt.displayName="ComboboxActions";function Ue(){let e=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Rt);if(e===null){let t=new Error("ComboboxActions is missing a parent <Combobox /> component.");throw Error.captureStackTrace&&Error.captureStackTrace(t,Ue),t}return e}function hr(e,t){return S(t.type,Ar,e,t)}var Or=react__WEBPACK_IMPORTED_MODULE_0__.Fragment,Ir=D(function(t,r){let{value:o,onChange:n,disabled:i=!1,...a}=t,l=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({value:o,onChange:n}),s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({static:!1,hold:!1}),u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({displayValue:void 0}),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useReducer)(hr,{comboboxState:1,comboboxPropsRef:l,optionsPropsRef:s,inputPropsRef:u,labelRef:(0,react__WEBPACK_IMPORTED_MODULE_0__.createRef)(),inputRef:(0,react__WEBPACK_IMPORTED_MODULE_0__.createRef)(),buttonRef:(0,react__WEBPACK_IMPORTED_MODULE_0__.createRef)(),optionsRef:(0,react__WEBPACK_IMPORTED_MODULE_0__.createRef)(),disabled:i,options:[],activeOptionIndex:null}),[{comboboxState:m,options:b,activeOptionIndex:T,optionsRef:y,inputRef:p,buttonRef:f},d]=c;x(()=>{l.current.value=o},[o,l]),x(()=>{l.current.onChange=n},[n,l]),x(()=>d({type:2,disabled:i}),[i]),w("mousedown",O=>{var N,K,V;let L=O.target;m===0&&(((N=f.current)==null?void 0:N.contains(L))||((K=p.current)==null?void 0:K.contains(L))||((V=y.current)==null?void 0:V.contains(L))||d({type:1}))});let P=T===null?null:b[T].dataRef.current.value,C=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:m===0,disabled:i,activeIndex:T,activeOption:P}),[m,i,b,T]),R=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{if(!p.current||o===void 0)return;let O=u.current.displayValue;typeof O=="function"?p.current.value=O(o):typeof o=="string"&&(p.current.value=o)},[o,p,u]),g=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(O=>{let L=b.find(K=>K.id===O);if(!L)return;let{dataRef:N}=L;l.current.onChange(N.current.value),R()},[b,l,p]),v=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{if(T!==null){let{dataRef:O}=b[T];l.current.onChange(O.current.value),R()}},[T,b,l,p]),h=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({selectOption:g,selectActiveOption:v}),[g,v]);return x(()=>{m===1&&R()},[R,m]),x(R,[R]),react__WEBPACK_IMPORTED_MODULE_0__.createElement(Rt.Provider,{value:h},react__WEBPACK_IMPORTED_MODULE_0__.createElement(vt.Provider,{value:c},react__WEBPACK_IMPORTED_MODULE_0__.createElement(W,{value:S(m,{[0]:0,[1]:1})},E({props:r===null?a:{...a,ref:r},slot:C,defaultTag:Or,name:"Combobox"}))))}),Lr="input",Dr=D(function(t,r){var R,g;let{value:o,onChange:n,displayValue:i,...a}=t,[l,s]=pe("Combobox.Input"),u=Ue(),c=I(l.inputRef,r),m=l.inputPropsRef,b=`headlessui-combobox-input-${A()}`,T=Q(),y=ke(n);x(()=>{m.current.displayValue=i},[i,m]);let p=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(v=>{switch(v.key){case"Enter":v.preventDefault(),v.stopPropagation(),u.selectActiveOption(),s({type:1});break;case"ArrowDown":return v.preventDefault(),v.stopPropagation(),S(l.comboboxState,{[0]:()=>s({type:3,focus:2}),[1]:()=>{s({type:0}),T.nextFrame(()=>{l.comboboxPropsRef.current.value||s({type:3,focus:0})})}});case"ArrowUp":return v.preventDefault(),v.stopPropagation(),S(l.comboboxState,{[0]:()=>s({type:3,focus:1}),[1]:()=>{s({type:0}),T.nextFrame(()=>{l.comboboxPropsRef.current.value||s({type:3,focus:3})})}});case"Home":case"PageUp":return v.preventDefault(),v.stopPropagation(),s({type:3,focus:0});case"End":case"PageDown":return v.preventDefault(),v.stopPropagation(),s({type:3,focus:3});case"Escape":return v.preventDefault(),l.optionsRef.current&&!l.optionsPropsRef.current.static&&v.stopPropagation(),s({type:1});case"Tab":u.selectActiveOption(),s({type:1});break}},[T,s,l,u]),f=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(v=>{var h;s({type:0}),(h=y.current)==null||h.call(y,v)},[s,y]),d=ee(()=>{if(!!l.labelRef.current)return[l.labelRef.current.id].join(" ")},[l.labelRef.current]),P=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:l.comboboxState===0,disabled:l.disabled}),[l]),C={ref:c,id:b,role:"combobox",type:"text","aria-controls":(R=l.optionsRef.current)==null?void 0:R.id,"aria-expanded":l.disabled?void 0:l.comboboxState===0,"aria-activedescendant":l.activeOptionIndex===null||(g=l.options[l.activeOptionIndex])==null?void 0:g.id,"aria-labelledby":d,disabled:l.disabled,onKeyDown:p,onChange:f};return E({props:{...a,...C},slot:P,defaultTag:Lr,name:"Combobox.Input"})}),Mr="button",Fr=D(function(t,r){var p;let[o,n]=pe("Combobox.Button"),i=Ue(),a=I(o.buttonRef,r),l=`headlessui-combobox-button-${A()}`,s=Q(),u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(f=>{switch(f.key){case"ArrowDown":return f.preventDefault(),f.stopPropagation(),o.comboboxState===1&&(n({type:0}),s.nextFrame(()=>{o.comboboxPropsRef.current.value||n({type:3,focus:0})})),s.nextFrame(()=>{var d;return(d=o.inputRef.current)==null?void 0:d.focus({preventScroll:!0})});case"ArrowUp":return f.preventDefault(),f.stopPropagation(),o.comboboxState===1&&(n({type:0}),s.nextFrame(()=>{o.comboboxPropsRef.current.value||n({type:3,focus:3})})),s.nextFrame(()=>{var d;return(d=o.inputRef.current)==null?void 0:d.focus({preventScroll:!0})});case"Escape":return f.preventDefault(),o.optionsRef.current&&!o.optionsPropsRef.current.static&&f.stopPropagation(),n({type:1}),s.nextFrame(()=>{var d;return(d=o.inputRef.current)==null?void 0:d.focus({preventScroll:!0})})}},[s,n,o,i]),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(f=>{if(G(f.currentTarget))return f.preventDefault();o.comboboxState===0?n({type:1}):(f.preventDefault(),n({type:0})),s.nextFrame(()=>{var d;return(d=o.inputRef.current)==null?void 0:d.focus({preventScroll:!0})})},[n,s,o]),m=ee(()=>{if(!!o.labelRef.current)return[o.labelRef.current.id,l].join(" ")},[o.labelRef.current,l]),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o.comboboxState===0,disabled:o.disabled}),[o]),T=t,y={ref:a,id:l,type:U(t,o.buttonRef),tabIndex:-1,"aria-haspopup":!0,"aria-controls":(p=o.optionsRef.current)==null?void 0:p.id,"aria-expanded":o.disabled?void 0:o.comboboxState===0,"aria-labelledby":m,disabled:o.disabled,onClick:c,onKeyDown:u};return E({props:{...T,...y},slot:b,defaultTag:Mr,name:"Combobox.Button"})}),wr="label";function kr(e){let[t]=pe("Combobox.Label"),r=`headlessui-combobox-label-${A()}`,o=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{var a;return(a=t.inputRef.current)==null?void 0:a.focus({preventScroll:!0})},[t.inputRef]),n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:t.comboboxState===0,disabled:t.disabled}),[t]),i={ref:t.labelRef,id:r,onClick:o};return E({props:{...e,...i},slot:n,defaultTag:wr,name:"Combobox.Label"})}var _r="ul",Gr=1|2,Hr=D(function(t,r){var y;let{hold:o=!1,...n}=t,[i]=pe("Combobox.Options"),{optionsPropsRef:a}=i,l=I(i.optionsRef,r),s=`headlessui-combobox-options-${A()}`,u=_(),c=(()=>u!==null?u===0:i.comboboxState===0)();x(()=>{var p;a.current.static=(p=t.static)!=null?p:!1},[a,t.static]),x(()=>{a.current.hold=o},[o,a]),se({container:i.optionsRef.current,enabled:i.comboboxState===0,accept(p){return p.getAttribute("role")==="option"?NodeFilter.FILTER_REJECT:p.hasAttribute("role")?NodeFilter.FILTER_SKIP:NodeFilter.FILTER_ACCEPT},walk(p){p.setAttribute("role","none")}});let m=ee(()=>{var p,f,d;return(d=(p=i.labelRef.current)==null?void 0:p.id)!=null?d:(f=i.buttonRef.current)==null?void 0:f.id},[i.labelRef.current,i.buttonRef.current]),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:i.comboboxState===0}),[i]),T={"aria-activedescendant":i.activeOptionIndex===null||(y=i.options[i.activeOptionIndex])==null?void 0:y.id,"aria-labelledby":m,role:"listbox",id:s,ref:l};return E({props:{...n,...T},slot:b,defaultTag:_r,features:Gr,visible:c,name:"Combobox.Options"})}),Ur="li";function Br(e){let{disabled:t=!1,value:r,...o}=e,[n,i]=pe("Combobox.Option"),a=Ue(),l=`headlessui-combobox-option-${A()}`,s=n.activeOptionIndex!==null?n.options[n.activeOptionIndex].id===l:!1,u=n.comboboxPropsRef.current.value===r,c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({disabled:t,value:r});x(()=>{c.current.disabled=t},[c,t]),x(()=>{c.current.value=r},[c,r]),x(()=>{var P,C;c.current.textValue=(C=(P=document.getElementById(l))==null?void 0:P.textContent)==null?void 0:C.toLowerCase()},[c,l]);let m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>a.selectOption(l),[a,l]);x(()=>(i({type:4,id:l,dataRef:c}),()=>i({type:5,id:l})),[c,l]),x(()=>{n.comboboxState===0&&(!u||i({type:3,focus:4,id:l}))},[n.comboboxState,u,l]),x(()=>{if(n.comboboxState!==0||!s)return;let P=k();return P.requestAnimationFrame(()=>{var C,R;(R=(C=document.getElementById(l))==null?void 0:C.scrollIntoView)==null||R.call(C,{block:"nearest"})}),P.dispose},[l,s,n.comboboxState,n.activeOptionIndex]);let b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(P=>{if(t)return P.preventDefault();m(),i({type:1}),k().nextFrame(()=>{var C;return(C=n.inputRef.current)==null?void 0:C.focus({preventScroll:!0})})},[i,n.inputRef,t,m]),T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{if(t)return i({type:3,focus:5});i({type:3,focus:4,id:l})},[t,l,i]),y=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{t||s||i({type:3,focus:4,id:l})},[t,s,l,i]),p=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{t||!s||n.optionsPropsRef.current.hold||i({type:3,focus:5})},[t,s,i,n.comboboxState,n.comboboxPropsRef]),f=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({active:s,selected:u,disabled:t}),[s,u,t]);return E({props:{...o,...{id:l,role:"option",tabIndex:t===!0?void 0:-1,"aria-disabled":t===!0?!0:void 0,"aria-selected":u===!0?!0:void 0,disabled:void 0,onClick:b,onFocus:T,onPointerMove:y,onMouseMove:y,onPointerLeave:p,onMouseLeave:p}},slot:f,defaultTag:Ur,name:"Combobox.Option"})}var Na=Object.assign(Ir,{Input:Dr,Button:Fr,Label:kr,Options:Hr,Option:Br});var Et=["[contentEditable=true]","[tabindex]","a[href]","area[href]","button:not([disabled])","iframe","input:not([disabled])","select:not([disabled])","textarea:not([disabled])"].map(e=>`${e}:not([tabindex='-1'])`).join(",");function xe(e=document.body){return e==null?[]:Array.from(e.querySelectorAll(Et))}function de(e,t=0){return e===document.body?!1:S(t,{[0](){return e.matches(Et)},[1](){let r=e;for(;r!==null;){if(r.matches(Et))return!0;r=r.parentElement}return!1}})}function ce(e){e==null||e.focus({preventScroll:!0})}function M(e,t){let r=Array.isArray(e)?e.slice().sort((c,m)=>{let b=c.compareDocumentPosition(m);return b&Node.DOCUMENT_POSITION_FOLLOWING?-1:b&Node.DOCUMENT_POSITION_PRECEDING?1:0}):xe(e),o=document.activeElement,n=(()=>{if(t&(1|4))return 1;if(t&(2|8))return-1;throw new Error("Missing Focus.First, Focus.Previous, Focus.Next or Focus.Last")})(),i=(()=>{if(t&1)return 0;if(t&2)return Math.max(0,r.indexOf(o))-1;if(t&4)return Math.max(0,r.indexOf(o))+1;if(t&8)return r.length-1;throw new Error("Missing Focus.First, Focus.Previous, Focus.Next or Focus.Last")})(),a=t&32?{preventScroll:!0}:{},l=0,s=r.length,u;do{if(l>=s||l+s<=0)return 0;let c=i+l;if(t&16)c=(c+s)%s;else{if(c<0)return 3;if(c>=s)return 1}u=r[c],u==null||u.focus(a),l+=n}while(u!==document.activeElement);return u.hasAttribute("tabindex")||u.setAttribute("tabindex","0"),2}function Be(){let e=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(!1);return (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>(e.current=!0,()=>{e.current=!1}),[]),e}function Ne(e,t=30,{initialFocus:r,containers:o}={}){let n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(typeof window!="undefined"?document.activeElement:null),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),a=Be(),l=Boolean(t&16),s=Boolean(t&2);(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{!l||(n.current=document.activeElement)},[l]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{if(!!l)return()=>{ce(n.current),n.current=null}},[l]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{if(!s||!e.current)return;let u=document.activeElement;if(r==null?void 0:r.current){if((r==null?void 0:r.current)===u){i.current=u;return}}else if(e.current.contains(u)){i.current=u;return}(r==null?void 0:r.current)?ce(r.current):M(e.current,1)===0&&console.warn("There are no focusable elements inside the <FocusTrap />"),i.current=document.activeElement},[e,r,s]),w("keydown",u=>{!(t&4)||!e.current||u.key==="Tab"&&(u.preventDefault(),M(e.current,(u.shiftKey?2:4)|16)===2&&(i.current=document.activeElement))}),w("focus",u=>{if(!(t&8))return;let c=new Set(o==null?void 0:o.current);if(c.add(e),!c.size)return;let m=i.current;if(!m||!a.current)return;let b=u.target;b&&b instanceof HTMLElement?Kr(c,b)?(i.current=b,ce(b)):(u.preventDefault(),u.stopPropagation(),ce(m)):ce(i.current)},!0)}function Kr(e,t){var r;for(let o of e)if((r=o.current)==null?void 0:r.contains(t))return!0;return!1}var fe=new Set,J=new Map;function po(e){e.setAttribute("aria-hidden","true"),e.inert=!0}function co(e){let t=J.get(e);!t||(t["aria-hidden"]===null?e.removeAttribute("aria-hidden"):e.setAttribute("aria-hidden",t["aria-hidden"]),e.inert=t.inert)}function fo(e,t=!0){x(()=>{if(!t||!e.current)return;let r=e.current;fe.add(r);for(let o of J.keys())o.contains(r)&&(co(o),J.delete(o));return document.querySelectorAll("body > *").forEach(o=>{if(o instanceof HTMLElement){for(let n of fe)if(o.contains(n))return;fe.size===1&&(J.set(o,{"aria-hidden":o.getAttribute("aria-hidden"),inert:o.inert}),po(o))}}),()=>{if(fe.delete(r),fe.size>0)document.querySelectorAll("body > *").forEach(o=>{if(o instanceof HTMLElement&&!J.has(o)){for(let n of fe)if(o.contains(n))return;J.set(o,{"aria-hidden":o.getAttribute("aria-hidden"),inert:o.inert}),po(o)}});else for(let o of J.keys())co(o),J.delete(o)}},[t])}var mo=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(!1);function bo(){return (0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(mo)}function At(e){return react__WEBPACK_IMPORTED_MODULE_0__.createElement(mo.Provider,{value:e.force},e.children)}function Xr(){let e=bo(),t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Po),[r,o]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(()=>{if(!e&&t!==null||typeof window=="undefined")return null;let n=document.getElementById("headlessui-portal-root");if(n)return n;let i=document.createElement("div");return i.setAttribute("id","headlessui-portal-root"),document.body.appendChild(i)});return (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{r!==null&&(document.body.contains(r)||document.body.appendChild(r))},[r]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{e||t!==null&&o(t.current)},[t,o,e]),r}var Jr=react__WEBPACK_IMPORTED_MODULE_0__.Fragment;function We(e){let t=e,r=Xr(),[o]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(()=>typeof window=="undefined"?null:document.createElement("div")),n=q();return x(()=>{if(!!r&&!!o)return r.appendChild(o),()=>{var i;!r||!o||(r.removeChild(o),r.childNodes.length<=0&&((i=r.parentElement)==null||i.removeChild(r)))}},[r,o]),n?!r||!o?null:(0,react_dom__WEBPACK_IMPORTED_MODULE_1__.createPortal)(E({props:t,defaultTag:Jr,name:"Portal"}),o):null}var Zr=react__WEBPACK_IMPORTED_MODULE_0__.Fragment,Po=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);function en(e){let{target:t,...r}=e;return react__WEBPACK_IMPORTED_MODULE_0__.createElement(Po.Provider,{value:t},E({props:r,defaultTag:Zr,name:"Popover.Group"}))}We.Group=en;var vo=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);function Ro(){let e=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(vo);if(e===null){let t=new Error("You used a <Description /> component, but it is not inside a relevant parent.");throw Error.captureStackTrace&&Error.captureStackTrace(t,Ro),t}return e}function re(){let[e,t]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)([]);return[e.length>0?e.join(" "):void 0,(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>function(o){let n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(a=>(t(l=>[...l,a]),()=>t(l=>{let s=l.slice(),u=s.indexOf(a);return u!==-1&&s.splice(u,1),s})),[]),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({register:n,slot:o.slot,name:o.name,props:o.props}),[n,o.slot,o.name,o.props]);return react__WEBPACK_IMPORTED_MODULE_0__.createElement(vo.Provider,{value:i},o.children)},[t])]}var an="p";function me(e){let t=Ro(),r=`headlessui-description-${A()}`;x(()=>t.register(r),[r,t.register]);let o=e,n={...t.props,id:r};return E({props:{...o,...n},slot:t.slot||{},defaultTag:an,name:t.name||"Description"})}var ht=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(()=>{});ht.displayName="StackContext";function cn(){return (0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(ht)}function Eo({children:e,onUpdate:t,type:r,element:o}){let n=cn(),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)((...a)=>{t==null||t(...a),n(...a)},[n,t]);return x(()=>(i(0,r,o),()=>i(1,r,o)),[i,r,o]),react__WEBPACK_IMPORTED_MODULE_0__.createElement(ht.Provider,{value:i},e)}var yn={[0](e,t){return e.titleId===t.id?e:{...e,titleId:t.id}}},Ve=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Ve.displayName="DialogContext";function It(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Ve);if(t===null){let r=new Error(`<${e} /> is missing a parent <${An.displayName} /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,It),r}return t}function gn(e,t){return S(t.type,yn,e,t)}var Pn="div",xn=1|2,vn=D(function(t,r){let{open:o,onClose:n,initialFocus:i,...a}=t,[l,s]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(0),u=_();o===void 0&&u!==null&&(o=S(u,{[0]:!0,[1]:!1}));let c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(new Set),m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),b=I(m,r),T=t.hasOwnProperty("open")||u!==null,y=t.hasOwnProperty("onClose");if(!T&&!y)throw new Error("You have to provide an `open` and an `onClose` prop to the `Dialog` component.");if(!T)throw new Error("You provided an `onClose` prop to the `Dialog`, but forgot an `open` prop.");if(!y)throw new Error("You provided an `open` prop to the `Dialog`, but forgot an `onClose` prop.");if(typeof o!="boolean")throw new Error(`You provided an \`open\` prop to the \`Dialog\`, but the value is not a boolean. Received: ${o}`);if(typeof n!="function")throw new Error(`You provided an \`onClose\` prop to the \`Dialog\`, but the value is not a function. Received: ${n}`);let p=o?0:1,f=(()=>u!==null?u===0:p===0)(),[d,P]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useReducer)(gn,{titleId:null,descriptionId:null}),C=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>n(!1),[n]),R=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(F=>P({type:0,id:F}),[P]),v=q()&&p===0,h=l>1,O=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Ve)!==null;Ne(m,v?S(h?"parent":"leaf",{parent:16,leaf:30}):1,{initialFocus:i,containers:c}),fo(m,h?v:!1),w("mousedown",F=>{var H;let $=F.target;p===0&&(h||((H=m.current)==null?void 0:H.contains($))||C())}),w("keydown",F=>{F.key==="Escape"&&p===0&&(h||(F.preventDefault(),F.stopPropagation(),C()))}),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{if(p!==0||O)return;let F=document.documentElement.style.overflow,$=document.documentElement.style.paddingRight,H=window.innerWidth-document.documentElement.clientWidth;return document.documentElement.style.overflow="hidden",document.documentElement.style.paddingRight=`${H}px`,()=>{document.documentElement.style.overflow=F,document.documentElement.style.paddingRight=$}},[p,O]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{if(p!==0||!m.current)return;let F=new IntersectionObserver($=>{for(let H of $)H.boundingClientRect.x===0&&H.boundingClientRect.y===0&&H.boundingClientRect.width===0&&H.boundingClientRect.height===0&&C()});return F.observe(m.current),()=>F.disconnect()},[p,m,C]);let[N,K]=re(),V=`headlessui-dialog-${A()}`,Fe=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>[{dialogState:p,close:C,setTitleId:R},d],[p,d,C,R]),ge=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:p===0}),[p]),we={ref:b,id:V,role:"dialog","aria-modal":p===0?!0:void 0,"aria-labelledby":d.titleId,"aria-describedby":N,onClick(F){F.stopPropagation()}},X=a;return react__WEBPACK_IMPORTED_MODULE_0__.createElement(Eo,{type:"Dialog",element:m,onUpdate:(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)((F,$,H)=>{$==="Dialog"&&S(F,{[0](){c.current.add(H),s(Pe=>Pe+1)},[1](){c.current.add(H),s(Pe=>Pe-1)}})},[])},react__WEBPACK_IMPORTED_MODULE_0__.createElement(At,{force:!0},react__WEBPACK_IMPORTED_MODULE_0__.createElement(We,null,react__WEBPACK_IMPORTED_MODULE_0__.createElement(Ve.Provider,{value:Fe},react__WEBPACK_IMPORTED_MODULE_0__.createElement(We.Group,{target:m},react__WEBPACK_IMPORTED_MODULE_0__.createElement(At,{force:!1},react__WEBPACK_IMPORTED_MODULE_0__.createElement(K,{slot:ge,name:"Dialog.Description"},E({props:{...X,...we},slot:ge,defaultTag:Pn,features:xn,visible:f,name:"Dialog"}))))))))}),Rn="div",En=D(function(t,r){let[{dialogState:o,close:n}]=It("Dialog.Overlay"),i=I(r),a=`headlessui-dialog-overlay-${A()}`,l=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(m=>{if(m.target===m.currentTarget){if(G(m.currentTarget))return m.preventDefault();m.preventDefault(),m.stopPropagation(),n()}},[n]),s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o===0}),[o]);return E({props:{...t,...{ref:i,id:a,"aria-hidden":!0,onClick:l}},slot:s,defaultTag:Rn,name:"Dialog.Overlay"})}),Cn="h2";function Sn(e){let[{dialogState:t,setTitleId:r}]=It("Dialog.Title"),o=`headlessui-dialog-title-${A()}`;(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>(r(o),()=>r(null)),[o,r]);let n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:t===0}),[t]);return E({props:{...e,...{id:o}},slot:n,defaultTag:Cn,name:"Dialog.Title"})}var An=Object.assign(vn,{Overlay:En,Title:Sn,Description:me});var Ln={[0]:e=>({...e,disclosureState:S(e.disclosureState,{[0]:1,[1]:0})}),[1]:e=>e.disclosureState===1?e:{...e,disclosureState:1},[4](e){return e.linkedPanel===!0?e:{...e,linkedPanel:!0}},[5](e){return e.linkedPanel===!1?e:{...e,linkedPanel:!1}},[2](e,t){return e.buttonId===t.buttonId?e:{...e,buttonId:t.buttonId}},[3](e,t){return e.panelId===t.panelId?e:{...e,panelId:t.panelId}}},Mt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Mt.displayName="DisclosureContext";function Ft(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Mt);if(t===null){let r=new Error(`<${e} /> is missing a parent <${Ye.name} /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,Ft),r}return t}var wt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);wt.displayName="DisclosureAPIContext";function Ao(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(wt);if(t===null){let r=new Error(`<${e} /> is missing a parent <${Ye.name} /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,Ao),r}return t}var kt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);kt.displayName="DisclosurePanelContext";function Dn(){return (0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(kt)}function Mn(e,t){return S(t.type,Ln,e,t)}var Fn=react__WEBPACK_IMPORTED_MODULE_0__.Fragment;function Ye(e){let{defaultOpen:t=!1,...r}=e,o=`headlessui-disclosure-button-${A()}`,n=`headlessui-disclosure-panel-${A()}`,i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useReducer)(Mn,{disclosureState:t?0:1,linkedPanel:!1,buttonId:o,panelId:n}),[{disclosureState:a},l]=i;(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>l({type:2,buttonId:o}),[o,l]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>l({type:3,panelId:n}),[n,l]);let s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(m=>{l({type:1});let b=(()=>m?m instanceof HTMLElement?m:m.current instanceof HTMLElement?m.current:document.getElementById(o):document.getElementById(o))();b==null||b.focus()},[l,o]),u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({close:s}),[s]),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:a===0,close:s}),[a,s]);return react__WEBPACK_IMPORTED_MODULE_0__.createElement(Mt.Provider,{value:i},react__WEBPACK_IMPORTED_MODULE_0__.createElement(wt.Provider,{value:u},react__WEBPACK_IMPORTED_MODULE_0__.createElement(W,{value:S(a,{[0]:0,[1]:1})},E({props:r,slot:c,defaultTag:Fn,name:"Disclosure"}))))}var wn="button",kn=D(function(t,r){let[o,n]=Ft("Disclosure.Button"),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),a=I(i,r),l=Dn(),s=l===null?!1:l===o.panelId,u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(f=>{var d;if(s){if(o.disclosureState===1)return;switch(f.key){case" ":case"Enter":f.preventDefault(),f.stopPropagation(),n({type:0}),(d=document.getElementById(o.buttonId))==null||d.focus();break}}else switch(f.key){case" ":case"Enter":f.preventDefault(),f.stopPropagation(),n({type:0});break}},[n,s,o.disclosureState,o.buttonId]),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(f=>{switch(f.key){case" ":f.preventDefault();break}},[]),m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(f=>{var d;G(f.currentTarget)||t.disabled||(s?(n({type:0}),(d=document.getElementById(o.buttonId))==null||d.focus()):n({type:0}))},[n,t.disabled,o.buttonId,s]),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o.disclosureState===0}),[o]),T=U(t,i),y=t,p=s?{ref:a,type:T,onKeyDown:u,onClick:m}:{ref:a,id:o.buttonId,type:T,"aria-expanded":t.disabled?void 0:o.disclosureState===0,"aria-controls":o.linkedPanel?o.panelId:void 0,onKeyDown:u,onKeyUp:c,onClick:m};return E({props:{...y,...p},slot:b,defaultTag:wn,name:"Disclosure.Button"})}),_n="div",Gn=1|2,Hn=D(function(t,r){let[o,n]=Ft("Disclosure.Panel"),{close:i}=Ao("Disclosure.Panel"),a=I(r,()=>{o.linkedPanel||n({type:4})}),l=_(),s=(()=>l!==null?l===0:o.disclosureState===0)();(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>()=>n({type:5}),[n]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{var b;o.disclosureState===1&&((b=t.unmount)!=null?b:!0)&&n({type:5})},[o.disclosureState,t.unmount,n]);let u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o.disclosureState===0,close:i}),[o,i]),c={ref:a,id:o.panelId},m=t;return react__WEBPACK_IMPORTED_MODULE_0__.createElement(kt.Provider,{value:o.panelId},E({props:{...m,...c},slot:u,defaultTag:_n,features:Gn,visible:s,name:"Disclosure.Panel"}))});Ye.Button=kn;Ye.Panel=Hn;var Bn="div";function yu(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),{initialFocus:r,...o}=e,n=q();return Ne(t,n?30:1,{initialFocus:r}),E({props:{...o,...{ref:t}},defaultTag:Bn,name:"FocusTrap"})}var $n={[1](e){return e.disabled||e.listboxState===1?e:{...e,activeOptionIndex:null,listboxState:1}},[0](e){return e.disabled||e.listboxState===0?e:{...e,listboxState:0}},[2](e,t){return e.disabled===t.disabled?e:{...e,disabled:t.disabled}},[3](e,t){return e.orientation===t.orientation?e:{...e,orientation:t.orientation}},[4](e,t){if(e.disabled||e.listboxState===1)return e;let r=ae(t,{resolveItems:()=>e.options,resolveActiveIndex:()=>e.activeOptionIndex,resolveId:o=>o.id,resolveDisabled:o=>o.dataRef.current.disabled});return e.searchQuery===""&&e.activeOptionIndex===r?e:{...e,searchQuery:"",activeOptionIndex:r}},[5]:(e,t)=>{if(e.disabled||e.listboxState===1)return e;let o=e.searchQuery!==""?0:1,n=e.searchQuery+t.value.toLowerCase(),a=(e.activeOptionIndex!==null?e.options.slice(e.activeOptionIndex+o).concat(e.options.slice(0,e.activeOptionIndex+o)):e.options).find(s=>{var u;return!s.dataRef.current.disabled&&((u=s.dataRef.current.textValue)==null?void 0:u.startsWith(n))}),l=a?e.options.indexOf(a):-1;return l===-1||l===e.activeOptionIndex?{...e,searchQuery:n}:{...e,searchQuery:n,activeOptionIndex:l}},[6](e){return e.disabled||e.listboxState===1||e.searchQuery===""?e:{...e,searchQuery:""}},[7]:(e,t)=>{var n;let r=Array.from((n=e.optionsRef.current)==null?void 0:n.querySelectorAll('[id^="headlessui-listbox-option-"]')).reduce((i,a,l)=>Object.assign(i,{[a.id]:l}),{}),o=[...e.options,{id:t.id,dataRef:t.dataRef}].sort((i,a)=>r[i.id]-r[a.id]);return{...e,options:o}},[8]:(e,t)=>{let r=e.options.slice(),o=e.activeOptionIndex!==null?r[e.activeOptionIndex]:null,n=r.findIndex(i=>i.id===t.id);return n!==-1&&r.splice(n,1),{...e,options:r,activeOptionIndex:(()=>n===e.activeOptionIndex||o===null?null:r.indexOf(o))()}}},Gt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Gt.displayName="ListboxContext";function Re(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Gt);if(t===null){let r=new Error(`<${e} /> is missing a parent <${Ee.name} /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,Re),r}return t}function Qn(e,t){return S(t.type,$n,e,t)}var qn=react__WEBPACK_IMPORTED_MODULE_0__.Fragment;function Ee(e){let{value:t,onChange:r,disabled:o=!1,horizontal:n=!1,...i}=e,a=n?"horizontal":"vertical",l=(0,react__WEBPACK_IMPORTED_MODULE_0__.useReducer)(Qn,{listboxState:1,propsRef:{current:{value:t,onChange:r}},labelRef:(0,react__WEBPACK_IMPORTED_MODULE_0__.createRef)(),buttonRef:(0,react__WEBPACK_IMPORTED_MODULE_0__.createRef)(),optionsRef:(0,react__WEBPACK_IMPORTED_MODULE_0__.createRef)(),disabled:o,orientation:a,options:[],searchQuery:"",activeOptionIndex:null}),[{listboxState:s,propsRef:u,optionsRef:c,buttonRef:m},b]=l;x(()=>{u.current.value=t},[t,u]),x(()=>{u.current.onChange=r},[r,u]),x(()=>b({type:2,disabled:o}),[o]),x(()=>b({type:3,orientation:a}),[a]),w("mousedown",y=>{var f,d,P;let p=y.target;s===0&&(((f=m.current)==null?void 0:f.contains(p))||((d=c.current)==null?void 0:d.contains(p))||(b({type:1}),de(p,1)||(y.preventDefault(),(P=m.current)==null||P.focus())))});let T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:s===0,disabled:o}),[s,o]);return react__WEBPACK_IMPORTED_MODULE_0__.createElement(Gt.Provider,{value:l},react__WEBPACK_IMPORTED_MODULE_0__.createElement(W,{value:S(s,{[0]:0,[1]:1})},E({props:i,slot:T,defaultTag:qn,name:"Listbox"})))}var zn="button",Yn=D(function(t,r){var p;let[o,n]=Re("Listbox.Button"),i=I(o.buttonRef,r),a=`headlessui-listbox-button-${A()}`,l=Q(),s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(f=>{switch(f.key){case" ":case"Enter":case"ArrowDown":f.preventDefault(),n({type:0}),l.nextFrame(()=>{o.propsRef.current.value||n({type:4,focus:0})});break;case"ArrowUp":f.preventDefault(),n({type:0}),l.nextFrame(()=>{o.propsRef.current.value||n({type:4,focus:3})});break}},[n,o,l]),u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(f=>{switch(f.key){case" ":f.preventDefault();break}},[]),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(f=>{if(G(f.currentTarget))return f.preventDefault();o.listboxState===0?(n({type:1}),l.nextFrame(()=>{var d;return(d=o.buttonRef.current)==null?void 0:d.focus({preventScroll:!0})})):(f.preventDefault(),n({type:0}))},[n,l,o]),m=ee(()=>{if(!!o.labelRef.current)return[o.labelRef.current.id,a].join(" ")},[o.labelRef.current,a]),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o.listboxState===0,disabled:o.disabled}),[o]),T=t,y={ref:i,id:a,type:U(t,o.buttonRef),"aria-haspopup":!0,"aria-controls":(p=o.optionsRef.current)==null?void 0:p.id,"aria-expanded":o.disabled?void 0:o.listboxState===0,"aria-labelledby":m,disabled:o.disabled,onKeyDown:s,onKeyUp:u,onClick:c};return E({props:{...T,...y},slot:b,defaultTag:zn,name:"Listbox.Button"})}),Xn="label";function Jn(e){let[t]=Re("Listbox.Label"),r=`headlessui-listbox-label-${A()}`,o=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{var a;return(a=t.buttonRef.current)==null?void 0:a.focus({preventScroll:!0})},[t.buttonRef]),n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:t.listboxState===0,disabled:t.disabled}),[t]),i={ref:t.labelRef,id:r,onClick:o};return E({props:{...e,...i},slot:n,defaultTag:Xn,name:"Listbox.Label"})}var Zn="ul",ei=1|2,ti=D(function(t,r){var f;let[o,n]=Re("Listbox.Options"),i=I(o.optionsRef,r),a=`headlessui-listbox-options-${A()}`,l=Q(),s=Q(),u=_(),c=(()=>u!==null?u===0:o.listboxState===0)();x(()=>{let d=o.optionsRef.current;!d||o.listboxState===0&&d!==document.activeElement&&d.focus({preventScroll:!0})},[o.listboxState,o.optionsRef]);let m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(d=>{switch(s.dispose(),d.key){case" ":if(o.searchQuery!=="")return d.preventDefault(),d.stopPropagation(),n({type:5,value:d.key});case"Enter":if(d.preventDefault(),d.stopPropagation(),n({type:1}),o.activeOptionIndex!==null){let{dataRef:P}=o.options[o.activeOptionIndex];o.propsRef.current.onChange(P.current.value)}k().nextFrame(()=>{var P;return(P=o.buttonRef.current)==null?void 0:P.focus({preventScroll:!0})});break;case S(o.orientation,{vertical:"ArrowDown",horizontal:"ArrowRight"}):return d.preventDefault(),d.stopPropagation(),n({type:4,focus:2});case S(o.orientation,{vertical:"ArrowUp",horizontal:"ArrowLeft"}):return d.preventDefault(),d.stopPropagation(),n({type:4,focus:1});case"Home":case"PageUp":return d.preventDefault(),d.stopPropagation(),n({type:4,focus:0});case"End":case"PageDown":return d.preventDefault(),d.stopPropagation(),n({type:4,focus:3});case"Escape":return d.preventDefault(),d.stopPropagation(),n({type:1}),l.nextFrame(()=>{var P;return(P=o.buttonRef.current)==null?void 0:P.focus({preventScroll:!0})});case"Tab":d.preventDefault(),d.stopPropagation();break;default:d.key.length===1&&(n({type:5,value:d.key}),s.setTimeout(()=>n({type:6}),350));break}},[l,n,s,o]),b=ee(()=>{var d,P,C;return(C=(d=o.labelRef.current)==null?void 0:d.id)!=null?C:(P=o.buttonRef.current)==null?void 0:P.id},[o.labelRef.current,o.buttonRef.current]),T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o.listboxState===0}),[o]),y={"aria-activedescendant":o.activeOptionIndex===null||(f=o.options[o.activeOptionIndex])==null?void 0:f.id,"aria-labelledby":b,"aria-orientation":o.orientation,id:a,onKeyDown:m,role:"listbox",tabIndex:0,ref:i};return E({props:{...t,...y},slot:T,defaultTag:Zn,features:ei,visible:c,name:"Listbox.Options"})}),oi="li";function ri(e){let{disabled:t=!1,value:r,...o}=e,[n,i]=Re("Listbox.Option"),a=`headlessui-listbox-option-${A()}`,l=n.activeOptionIndex!==null?n.options[n.activeOptionIndex].id===a:!1,s=n.propsRef.current.value===r,u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({disabled:t,value:r});x(()=>{u.current.disabled=t},[u,t]),x(()=>{u.current.value=r},[u,r]),x(()=>{var d,P;u.current.textValue=(P=(d=document.getElementById(a))==null?void 0:d.textContent)==null?void 0:P.toLowerCase()},[u,a]);let c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>n.propsRef.current.onChange(r),[n.propsRef,r]);x(()=>(i({type:7,id:a,dataRef:u}),()=>i({type:8,id:a})),[u,a]),x(()=>{var d,P;n.listboxState===0&&(!s||(i({type:4,focus:4,id:a}),(P=(d=document.getElementById(a))==null?void 0:d.focus)==null||P.call(d)))},[n.listboxState]),x(()=>{if(n.listboxState!==0||!l)return;let d=k();return d.requestAnimationFrame(()=>{var P,C;(C=(P=document.getElementById(a))==null?void 0:P.scrollIntoView)==null||C.call(P,{block:"nearest"})}),d.dispose},[a,l,n.listboxState,n.activeOptionIndex]);let m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(d=>{if(t)return d.preventDefault();c(),i({type:1}),k().nextFrame(()=>{var P;return(P=n.buttonRef.current)==null?void 0:P.focus({preventScroll:!0})})},[i,n.buttonRef,t,c]),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{if(t)return i({type:4,focus:5});i({type:4,focus:4,id:a})},[t,a,i]),T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{t||l||i({type:4,focus:4,id:a})},[t,l,a,i]),y=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{t||!l||i({type:4,focus:5})},[t,l,i]),p=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({active:l,selected:s,disabled:t}),[l,s,t]);return E({props:{...o,...{id:a,role:"option",tabIndex:t===!0?void 0:-1,"aria-disabled":t===!0?!0:void 0,"aria-selected":s===!0?!0:void 0,disabled:void 0,onClick:m,onFocus:b,onPointerMove:T,onMouseMove:T,onPointerLeave:y,onMouseLeave:y}},slot:p,defaultTag:oi,name:"Listbox.Option"})}Ee.Button=Yn;Ee.Label=Jn;Ee.Options=ti;Ee.Option=ri;var ui={[1](e){return e.menuState===1?e:{...e,activeItemIndex:null,menuState:1}},[0](e){return e.menuState===0?e:{...e,menuState:0}},[2]:(e,t)=>{let r=ae(t,{resolveItems:()=>e.items,resolveActiveIndex:()=>e.activeItemIndex,resolveId:o=>o.id,resolveDisabled:o=>o.dataRef.current.disabled});return e.searchQuery===""&&e.activeItemIndex===r?e:{...e,searchQuery:"",activeItemIndex:r}},[3]:(e,t)=>{let o=e.searchQuery!==""?0:1,n=e.searchQuery+t.value.toLowerCase(),a=(e.activeItemIndex!==null?e.items.slice(e.activeItemIndex+o).concat(e.items.slice(0,e.activeItemIndex+o)):e.items).find(s=>{var u;return((u=s.dataRef.current.textValue)==null?void 0:u.startsWith(n))&&!s.dataRef.current.disabled}),l=a?e.items.indexOf(a):-1;return l===-1||l===e.activeItemIndex?{...e,searchQuery:n}:{...e,searchQuery:n,activeItemIndex:l}},[4](e){return e.searchQuery===""?e:{...e,searchQuery:"",searchActiveItemIndex:null}},[5]:(e,t)=>{var n;let r=Array.from((n=e.itemsRef.current)==null?void 0:n.querySelectorAll('[id^="headlessui-menu-item-"]')).reduce((i,a,l)=>Object.assign(i,{[a.id]:l}),{}),o=[...e.items,{id:t.id,dataRef:t.dataRef}].sort((i,a)=>r[i.id]-r[a.id]);return{...e,items:o}},[6]:(e,t)=>{let r=e.items.slice(),o=e.activeItemIndex!==null?r[e.activeItemIndex]:null,n=r.findIndex(i=>i.id===t.id);return n!==-1&&r.splice(n,1),{...e,items:r,activeItemIndex:(()=>n===e.activeItemIndex||o===null?null:r.indexOf(o))()}}},Ht=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Ht.displayName="MenuContext";function Je(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Ht);if(t===null){let r=new Error(`<${e} /> is missing a parent <${Ze.name} /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,Je),r}return t}function pi(e,t){return S(t.type,ui,e,t)}var di=react__WEBPACK_IMPORTED_MODULE_0__.Fragment;function Ze(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useReducer)(pi,{menuState:1,buttonRef:(0,react__WEBPACK_IMPORTED_MODULE_0__.createRef)(),itemsRef:(0,react__WEBPACK_IMPORTED_MODULE_0__.createRef)(),items:[],searchQuery:"",activeItemIndex:null}),[{menuState:r,itemsRef:o,buttonRef:n},i]=t;w("mousedown",l=>{var u,c,m;let s=l.target;r===0&&(((u=n.current)==null?void 0:u.contains(s))||((c=o.current)==null?void 0:c.contains(s))||(i({type:1}),de(s,1)||(l.preventDefault(),(m=n.current)==null||m.focus())))});let a=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:r===0}),[r]);return react__WEBPACK_IMPORTED_MODULE_0__.createElement(Ht.Provider,{value:t},react__WEBPACK_IMPORTED_MODULE_0__.createElement(W,{value:S(r,{[0]:0,[1]:1})},E({props:e,slot:a,defaultTag:di,name:"Menu"})))}var ci="button",fi=D(function(t,r){var y;let[o,n]=Je("Menu.Button"),i=I(o.buttonRef,r),a=`headlessui-menu-button-${A()}`,l=Q(),s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(p=>{switch(p.key){case" ":case"Enter":case"ArrowDown":p.preventDefault(),p.stopPropagation(),n({type:0}),l.nextFrame(()=>n({type:2,focus:0}));break;case"ArrowUp":p.preventDefault(),p.stopPropagation(),n({type:0}),l.nextFrame(()=>n({type:2,focus:3}));break}},[n,l]),u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(p=>{switch(p.key){case" ":p.preventDefault();break}},[]),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(p=>{if(G(p.currentTarget))return p.preventDefault();t.disabled||(o.menuState===0?(n({type:1}),l.nextFrame(()=>{var f;return(f=o.buttonRef.current)==null?void 0:f.focus({preventScroll:!0})})):(p.preventDefault(),p.stopPropagation(),n({type:0})))},[n,l,o,t.disabled]),m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o.menuState===0}),[o]),b=t,T={ref:i,id:a,type:U(t,o.buttonRef),"aria-haspopup":!0,"aria-controls":(y=o.itemsRef.current)==null?void 0:y.id,"aria-expanded":t.disabled?void 0:o.menuState===0,onKeyDown:s,onKeyUp:u,onClick:c};return E({props:{...b,...T},slot:m,defaultTag:ci,name:"Menu.Button"})}),mi="div",bi=1|2,Ti=D(function(t,r){var p,f;let[o,n]=Je("Menu.Items"),i=I(o.itemsRef,r),a=`headlessui-menu-items-${A()}`,l=Q(),s=_(),u=(()=>s!==null?s===0:o.menuState===0)();(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{let d=o.itemsRef.current;!d||o.menuState===0&&d!==document.activeElement&&d.focus({preventScroll:!0})},[o.menuState,o.itemsRef]),se({container:o.itemsRef.current,enabled:o.menuState===0,accept(d){return d.getAttribute("role")==="menuitem"?NodeFilter.FILTER_REJECT:d.hasAttribute("role")?NodeFilter.FILTER_SKIP:NodeFilter.FILTER_ACCEPT},walk(d){d.setAttribute("role","none")}});let c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(d=>{var P;switch(l.dispose(),d.key){case" ":if(o.searchQuery!=="")return d.preventDefault(),d.stopPropagation(),n({type:3,value:d.key});case"Enter":if(d.preventDefault(),d.stopPropagation(),n({type:1}),o.activeItemIndex!==null){let{id:C}=o.items[o.activeItemIndex];(P=document.getElementById(C))==null||P.click()}k().nextFrame(()=>{var C;return(C=o.buttonRef.current)==null?void 0:C.focus({preventScroll:!0})});break;case"ArrowDown":return d.preventDefault(),d.stopPropagation(),n({type:2,focus:2});case"ArrowUp":return d.preventDefault(),d.stopPropagation(),n({type:2,focus:1});case"Home":case"PageUp":return d.preventDefault(),d.stopPropagation(),n({type:2,focus:0});case"End":case"PageDown":return d.preventDefault(),d.stopPropagation(),n({type:2,focus:3});case"Escape":d.preventDefault(),d.stopPropagation(),n({type:1}),k().nextFrame(()=>{var C;return(C=o.buttonRef.current)==null?void 0:C.focus({preventScroll:!0})});break;case"Tab":d.preventDefault(),d.stopPropagation();break;default:d.key.length===1&&(n({type:3,value:d.key}),l.setTimeout(()=>n({type:4}),350));break}},[n,l,o]),m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(d=>{switch(d.key){case" ":d.preventDefault();break}},[]),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o.menuState===0}),[o]),T={"aria-activedescendant":o.activeItemIndex===null||(p=o.items[o.activeItemIndex])==null?void 0:p.id,"aria-labelledby":(f=o.buttonRef.current)==null?void 0:f.id,id:a,onKeyDown:c,onKeyUp:m,role:"menu",tabIndex:0,ref:i};return E({props:{...t,...T},slot:b,defaultTag:mi,features:bi,visible:u,name:"Menu.Items"})}),yi=react__WEBPACK_IMPORTED_MODULE_0__.Fragment;function gi(e){let{disabled:t=!1,onClick:r,...o}=e,[n,i]=Je("Menu.Item"),a=`headlessui-menu-item-${A()}`,l=n.activeItemIndex!==null?n.items[n.activeItemIndex].id===a:!1;x(()=>{if(n.menuState!==0||!l)return;let p=k();return p.requestAnimationFrame(()=>{var f,d;(d=(f=document.getElementById(a))==null?void 0:f.scrollIntoView)==null||d.call(f,{block:"nearest"})}),p.dispose},[a,l,n.menuState,n.activeItemIndex]);let s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({disabled:t});x(()=>{s.current.disabled=t},[s,t]),x(()=>{var p,f;s.current.textValue=(f=(p=document.getElementById(a))==null?void 0:p.textContent)==null?void 0:f.toLowerCase()},[s,a]),x(()=>(i({type:5,id:a,dataRef:s}),()=>i({type:6,id:a})),[s,a]);let u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(p=>{if(t)return p.preventDefault();if(i({type:1}),k().nextFrame(()=>{var f;return(f=n.buttonRef.current)==null?void 0:f.focus({preventScroll:!0})}),r)return r(p)},[i,n.buttonRef,t,r]),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{if(t)return i({type:2,focus:5});i({type:2,focus:4,id:a})},[t,a,i]),m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{t||l||i({type:2,focus:4,id:a})},[t,l,a,i]),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{t||!l||i({type:2,focus:5})},[t,l,i]),T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({active:l,disabled:t}),[l,t]);return E({props:{...o,...{id:a,role:"menuitem",tabIndex:t===!0?void 0:-1,"aria-disabled":t===!0?!0:void 0,disabled:void 0,onClick:u,onFocus:c,onPointerMove:m,onMouseMove:m,onPointerLeave:b,onMouseLeave:b}},slot:T,defaultTag:yi,name:"Menu.Item"})}Ze.Button=fi;Ze.Items=Ti;Ze.Item=gi;var vi={[0]:e=>({...e,popoverState:S(e.popoverState,{[0]:1,[1]:0})}),[1](e){return e.popoverState===1?e:{...e,popoverState:1}},[2](e,t){return e.button===t.button?e:{...e,button:t.button}},[3](e,t){return e.buttonId===t.buttonId?e:{...e,buttonId:t.buttonId}},[4](e,t){return e.panel===t.panel?e:{...e,panel:t.panel}},[5](e,t){return e.panelId===t.panelId?e:{...e,panelId:t.panelId}}},Ut=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Ut.displayName="PopoverContext";function ot(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Ut);if(t===null){let r=new Error(`<${e} /> is missing a parent <${Te.name} /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,ot),r}return t}var Bt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Bt.displayName="PopoverAPIContext";function Mo(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Bt);if(t===null){let r=new Error(`<${e} /> is missing a parent <${Te.name} /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,Mo),r}return t}var Nt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Nt.displayName="PopoverGroupContext";function Fo(){return (0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Nt)}var Wt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);Wt.displayName="PopoverPanelContext";function Ri(){return (0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(Wt)}function Ei(e,t){return S(t.type,vi,e,t)}var Ci="div";function Te(e){let t=`headlessui-popover-button-${A()}`,r=`headlessui-popover-panel-${A()}`,o=(0,react__WEBPACK_IMPORTED_MODULE_0__.useReducer)(Ei,{popoverState:1,button:null,buttonId:t,panel:null,panelId:r}),[{popoverState:n,button:i,panel:a},l]=o;(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>l({type:3,buttonId:t}),[t,l]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>l({type:5,panelId:r}),[r,l]);let s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({buttonId:t,panelId:r,close:()=>l({type:1})}),[t,r,l]),u=Fo(),c=u==null?void 0:u.registerPopover,m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{var p;return(p=u==null?void 0:u.isFocusWithinPopoverGroup())!=null?p:(i==null?void 0:i.contains(document.activeElement))||(a==null?void 0:a.contains(document.activeElement))},[u,i,a]);(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>c==null?void 0:c(s),[c,s]),w("focus",()=>{n===0&&(m()||!i||!a||l({type:1}))},!0),w("mousedown",p=>{let f=p.target;n===0&&((i==null?void 0:i.contains(f))||(a==null?void 0:a.contains(f))||(l({type:1}),de(f,1)||(p.preventDefault(),i==null||i.focus())))});let b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(p=>{l({type:1});let f=(()=>p?p instanceof HTMLElement?p:p.current instanceof HTMLElement?p.current:i:i)();f==null||f.focus()},[l,i]),T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({close:b}),[b]),y=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:n===0,close:b}),[n,b]);return react__WEBPACK_IMPORTED_MODULE_0__.createElement(Ut.Provider,{value:o},react__WEBPACK_IMPORTED_MODULE_0__.createElement(Bt.Provider,{value:T},react__WEBPACK_IMPORTED_MODULE_0__.createElement(W,{value:S(n,{[0]:0,[1]:1})},E({props:e,slot:y,defaultTag:Ci,name:"Popover"}))))}var Si="button",Ai=D(function(t,r){let[o,n]=ot("Popover.Button"),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),a=Fo(),l=a==null?void 0:a.closeOthers,s=Ri(),u=s===null?!1:s===o.panelId,c=I(i,r,u?null:g=>n({type:2,button:g})),m=I(i,r),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(typeof window=="undefined"?null:document.activeElement);w("focus",()=>{T.current=b.current,b.current=document.activeElement},!0);let y=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(g=>{var v,h;if(u){if(o.popoverState===1)return;switch(g.key){case" ":case"Enter":g.preventDefault(),g.stopPropagation(),n({type:1}),(v=o.button)==null||v.focus();break}}else switch(g.key){case" ":case"Enter":g.preventDefault(),g.stopPropagation(),o.popoverState===1&&(l==null||l(o.buttonId)),n({type:0});break;case"Escape":if(o.popoverState!==0)return l==null?void 0:l(o.buttonId);if(!i.current||!i.current.contains(document.activeElement))return;g.preventDefault(),g.stopPropagation(),n({type:1});break;case"Tab":if(o.popoverState!==0||!o.panel||!o.button)return;if(g.shiftKey){if(!T.current||((h=o.button)==null?void 0:h.contains(T.current))||o.panel.contains(T.current))return;let O=xe(),L=O.indexOf(T.current);if(O.indexOf(o.button)>L)return;g.preventDefault(),g.stopPropagation(),M(o.panel,8)}else g.preventDefault(),g.stopPropagation(),M(o.panel,1);break}},[n,o.popoverState,o.buttonId,o.button,o.panel,i,l,u]),p=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(g=>{var v;if(!u&&(g.key===" "&&g.preventDefault(),o.popoverState===0&&!!o.panel&&!!o.button))switch(g.key){case"Tab":if(!T.current||((v=o.button)==null?void 0:v.contains(T.current))||o.panel.contains(T.current))return;let h=xe(),O=h.indexOf(T.current);if(h.indexOf(o.button)>O)return;g.preventDefault(),g.stopPropagation(),M(o.panel,8);break}},[o.popoverState,o.panel,o.button,u]),f=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(g=>{var v,h;G(g.currentTarget)||t.disabled||(u?(n({type:1}),(v=o.button)==null||v.focus()):(o.popoverState===1&&(l==null||l(o.buttonId)),(h=o.button)==null||h.focus(),n({type:0})))},[n,o.button,o.popoverState,o.buttonId,t.disabled,l,u]),d=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o.popoverState===0}),[o]),P=U(t,i),C=t,R=u?{ref:m,type:P,onKeyDown:y,onClick:f}:{ref:c,id:o.buttonId,type:P,"aria-expanded":t.disabled?void 0:o.popoverState===0,"aria-controls":o.panel?o.panelId:void 0,onKeyDown:y,onKeyUp:p,onClick:f};return E({props:{...C,...R},slot:d,defaultTag:Si,name:"Popover.Button"})}),hi="div",Oi=1|2,Ii=D(function(t,r){let[{popoverState:o},n]=ot("Popover.Overlay"),i=I(r),a=`headlessui-popover-overlay-${A()}`,l=_(),s=(()=>l!==null?l===0:o===0)(),u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(T=>{if(G(T.currentTarget))return T.preventDefault();n({type:1})},[n]),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:o===0}),[o]);return E({props:{...t,...{ref:i,id:a,"aria-hidden":!0,onClick:u}},slot:c,defaultTag:hi,features:Oi,visible:s,name:"Popover.Overlay"})}),Li="div",Di=1|2,Mi=D(function(t,r){let{focus:o=!1,...n}=t,[i,a]=ot("Popover.Panel"),{close:l}=Mo("Popover.Panel"),s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),u=I(s,r,p=>{a({type:4,panel:p})}),c=_(),m=(()=>c!==null?c===0:i.popoverState===0)(),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(p=>{var f;switch(p.key){case"Escape":if(i.popoverState!==0||!s.current||!s.current.contains(document.activeElement))return;p.preventDefault(),p.stopPropagation(),a({type:1}),(f=i.button)==null||f.focus();break}},[i,s,a]);(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>()=>a({type:4,panel:null}),[a]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{var p;t.static||i.popoverState===1&&((p=t.unmount)!=null?p:!0)&&a({type:4,panel:null})},[i.popoverState,t.unmount,t.static,a]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{if(!o||i.popoverState!==0||!s.current)return;let p=document.activeElement;s.current.contains(p)||M(s.current,1)},[o,s,i.popoverState]),w("keydown",p=>{var d;if(i.popoverState!==0||!s.current||p.key!=="Tab"||!document.activeElement||!s.current||!s.current.contains(document.activeElement))return;p.preventDefault();let f=M(s.current,p.shiftKey?2:4);if(f===3)return(d=i.button)==null?void 0:d.focus();if(f===1){if(!i.button)return;let P=xe(),C=P.indexOf(i.button),R=P.splice(C+1).filter(g=>{var v;return!((v=s.current)==null?void 0:v.contains(g))});M(R,1)===0&&M(document.body,1)}}),w("focus",()=>{var p;!o||i.popoverState===0&&(!s.current||((p=s.current)==null?void 0:p.contains(document.activeElement))||a({type:1}))},!0);let T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({open:i.popoverState===0,close:l}),[i,l]),y={ref:u,id:i.panelId,onKeyDown:b};return react__WEBPACK_IMPORTED_MODULE_0__.createElement(Wt.Provider,{value:i.panelId},E({props:{...n,...y},slot:T,defaultTag:Li,features:Di,visible:m,name:"Popover.Panel"}))}),Fi="div";function wi(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),[r,o]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)([]),n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(b=>{o(T=>{let y=T.indexOf(b);if(y!==-1){let p=T.slice();return p.splice(y,1),p}return T})},[o]),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(b=>(o(T=>[...T,b]),()=>n(b)),[o,n]),a=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{var T;let b=document.activeElement;return((T=t.current)==null?void 0:T.contains(b))?!0:r.some(y=>{var p,f;return((p=document.getElementById(y.buttonId))==null?void 0:p.contains(b))||((f=document.getElementById(y.panelId))==null?void 0:f.contains(b))})},[t,r]),l=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(b=>{for(let T of r)T.buttonId!==b&&T.close()},[r]),s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({registerPopover:i,unregisterPopover:n,isFocusWithinPopoverGroup:a,closeOthers:l}),[i,n,a,l]),u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({}),[]),c={ref:t},m=e;return react__WEBPACK_IMPORTED_MODULE_0__.createElement(Nt.Provider,{value:s},E({props:{...m,...c},slot:u,defaultTag:Fi,name:"Popover.Group"}))}Te.Button=Ai;Te.Overlay=Ii;Te.Panel=Mi;Te.Group=wi;function wo(e=0){let[t,r]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(e),o=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(l=>r(s=>s|l),[r]),n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(l=>Boolean(t&l),[t]),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(l=>r(s=>s&~l),[r]),a=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(l=>r(s=>s^l),[r]);return{addFlag:o,hasFlag:n,removeFlag:i,toggleFlag:a}}var _o=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);function Go(){let e=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(_o);if(e===null){let t=new Error("You used a <Label /> component, but it is not inside a relevant parent.");throw Error.captureStackTrace&&Error.captureStackTrace(t,Go),t}return e}function Ae(){let[e,t]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)([]);return[e.length>0?e.join(" "):void 0,(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>function(o){let n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(a=>(t(l=>[...l,a]),()=>t(l=>{let s=l.slice(),u=s.indexOf(a);return u!==-1&&s.splice(u,1),s})),[]),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({register:n,slot:o.slot,name:o.name,props:o.props}),[n,o.slot,o.name,o.props]);return react__WEBPACK_IMPORTED_MODULE_0__.createElement(_o.Provider,{value:i},o.children)},[t])]}var Ni="label";function nt(e){let{passive:t=!1,...r}=e,o=Go(),n=`headlessui-label-${A()}`;x(()=>o.register(n),[n,o.register]);let i={...o.props,id:n},a={...r,...i};return t&&delete a.onClick,E({props:a,slot:o.slot||{},defaultTag:Ni,name:o.name||"Label"})}var Vi={[0](e,t){return{...e,options:[...e.options,{id:t.id,element:t.element,propsRef:t.propsRef}]}},[1](e,t){let r=e.options.slice(),o=e.options.findIndex(n=>n.id===t.id);return o===-1?e:(r.splice(o,1),{...e,options:r})}},jt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);jt.displayName="RadioGroupContext";function Ho(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(jt);if(t===null){let r=new Error(`<${e} /> is missing a parent <${lt.name} /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,Ho),r}return t}function $i(e,t){return S(t.type,Vi,e,t)}var Qi="div";function lt(e){let{value:t,onChange:r,disabled:o=!1,...n}=e,[{options:i},a]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useReducer)($i,{options:[]}),[l,s]=Ae(),[u,c]=re(),m=`headlessui-radiogroup-${A()}`,b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>i.find(R=>!R.propsRef.current.disabled),[i]),y=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>i.some(R=>R.propsRef.current.value===t),[i,t]),p=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(R=>{var v;if(o||R===t)return!1;let g=(v=i.find(h=>h.propsRef.current.value===R))==null?void 0:v.propsRef.current;return(g==null?void 0:g.disabled)?!1:(r(R),!0)},[r,t,o,i]);se({container:b.current,accept(R){return R.getAttribute("role")==="radio"?NodeFilter.FILTER_REJECT:R.hasAttribute("role")?NodeFilter.FILTER_SKIP:NodeFilter.FILTER_ACCEPT},walk(R){R.setAttribute("role","none")}});let f=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(R=>{if(!b.current)return;let v=i.filter(h=>h.propsRef.current.disabled===!1).map(h=>h.element.current);switch(R.key){case"ArrowLeft":case"ArrowUp":if(R.preventDefault(),R.stopPropagation(),M(v,2|16)===2){let O=i.find(L=>L.element.current===document.activeElement);O&&p(O.propsRef.current.value)}break;case"ArrowRight":case"ArrowDown":if(R.preventDefault(),R.stopPropagation(),M(v,4|16)===2){let O=i.find(L=>L.element.current===document.activeElement);O&&p(O.propsRef.current.value)}break;case" ":{R.preventDefault(),R.stopPropagation();let h=i.find(O=>O.element.current===document.activeElement);h&&p(h.propsRef.current.value)}break}},[b,i,p]),d=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(R=>(a({type:0,...R}),()=>a({type:1,id:R.id})),[a]),P=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({registerOption:d,firstOption:T,containsCheckedOption:y,change:p,disabled:o,value:t}),[d,T,y,p,o,t]),C={ref:b,id:m,role:"radiogroup","aria-labelledby":l,"aria-describedby":u,onKeyDown:f};return react__WEBPACK_IMPORTED_MODULE_0__.createElement(c,{name:"RadioGroup.Description"},react__WEBPACK_IMPORTED_MODULE_0__.createElement(s,{name:"RadioGroup.Label"},react__WEBPACK_IMPORTED_MODULE_0__.createElement(jt.Provider,{value:P},E({props:{...n,...C},defaultTag:Qi,name:"RadioGroup"}))))}var qi="div";function zi(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),r=`headlessui-radiogroup-option-${A()}`,[o,n]=Ae(),[i,a]=re(),{addFlag:l,removeFlag:s,hasFlag:u}=wo(1),{value:c,disabled:m=!1,...b}=e,T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({value:c,disabled:m});x(()=>{T.current.value=c},[c,T]),x(()=>{T.current.disabled=m},[m,T]);let{registerOption:y,disabled:p,change:f,firstOption:d,containsCheckedOption:P,value:C}=Ho("RadioGroup.Option");x(()=>y({id:r,element:t,propsRef:T}),[r,y,t,e]);let R=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{var V;!f(c)||(l(2),(V=t.current)==null||V.focus())},[l,f,c]),g=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>l(2),[l]),v=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>s(2),[s]),h=(d==null?void 0:d.id)===r,O=p||m,L=C===c,N={ref:t,id:r,role:"radio","aria-checked":L?"true":"false","aria-labelledby":o,"aria-describedby":i,"aria-disabled":O?!0:void 0,tabIndex:(()=>O?-1:L||!P&&h?0:-1)(),onClick:O?void 0:R,onFocus:O?void 0:g,onBlur:O?void 0:v},K=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({checked:L,disabled:O,active:u(2)}),[L,O,u]);return react__WEBPACK_IMPORTED_MODULE_0__.createElement(a,{name:"RadioGroup.Description"},react__WEBPACK_IMPORTED_MODULE_0__.createElement(n,{name:"RadioGroup.Label"},E({props:{...b,...N},slot:K,defaultTag:qi,name:"RadioGroup.Option"})))}lt.Option=zi;lt.Label=nt;lt.Description=me;var $t=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);$t.displayName="GroupContext";var tl=react__WEBPACK_IMPORTED_MODULE_0__.Fragment;function ol(e){let[t,r]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null),[o,n]=Ae(),[i,a]=re(),l=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({switch:t,setSwitch:r,labelledby:o,describedby:i}),[t,r,o,i]);return react__WEBPACK_IMPORTED_MODULE_0__.createElement(a,{name:"Switch.Description"},react__WEBPACK_IMPORTED_MODULE_0__.createElement(n,{name:"Switch.Label",props:{onClick(){!t||(t.click(),t.focus({preventScroll:!0}))}}},react__WEBPACK_IMPORTED_MODULE_0__.createElement($t.Provider,{value:l},E({props:e,defaultTag:tl,name:"Switch.Group"}))))}var rl="button";function Qt(e){let{checked:t,onChange:r,...o}=e,n=`headlessui-switch-${A()}`,i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)($t),a=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),l=I(a,i===null?null:i.setSwitch),s=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>r(!t),[r,t]),u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(y=>{if(G(y.currentTarget))return y.preventDefault();y.preventDefault(),s()},[s]),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(y=>{y.key!=="Tab"&&y.preventDefault(),y.key===" "&&s()},[s]),m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(y=>y.preventDefault(),[]),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({checked:t}),[t]),T={id:n,ref:l,role:"switch",type:U(e,a),tabIndex:0,"aria-checked":t,"aria-labelledby":i==null?void 0:i.labelledby,"aria-describedby":i==null?void 0:i.describedby,onClick:u,onKeyUp:c,onKeyPress:m};return E({props:{...o,...T},slot:b,defaultTag:rl,name:"Switch"})}Qt.Group=ol;Qt.Label=nt;Qt.Description=me;var ul={[0](e,t){return e.selectedIndex===t.index?e:{...e,selectedIndex:t.index}},[1](e,t){return e.orientation===t.orientation?e:{...e,orientation:t.orientation}},[2](e,t){return e.activation===t.activation?e:{...e,activation:t.activation}},[3](e,t){return e.tabs.includes(t.tab)?e:{...e,tabs:[...e.tabs,t.tab]}},[4](e,t){return{...e,tabs:e.tabs.filter(r=>r!==t.tab)}},[5](e,t){return e.panels.includes(t.panel)?e:{...e,panels:[...e.panels,t.panel]}},[6](e,t){return{...e,panels:e.panels.filter(r=>r!==t.panel)}},[7](e){return{...e}}},zt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);zt.displayName="TabsContext";function Le(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(zt);if(t===null){let r=new Error(`<${e} /> is missing a parent <Tab.Group /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,Le),r}return t}function pl(e,t){return S(t.type,ul,e,t)}var dl=react__WEBPACK_IMPORTED_MODULE_0__.Fragment;function cl(e){let{defaultIndex:t=0,vertical:r=!1,manual:o=!1,onChange:n,selectedIndex:i=null,...a}=e,l=r?"vertical":"horizontal",s=o?"manual":"auto",[u,c]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useReducer)(pl,{selectedIndex:null,tabs:[],panels:[],orientation:l,activation:s}),m=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({selectedIndex:u.selectedIndex}),[u.selectedIndex]),b=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(()=>{});(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{c({type:1,orientation:l})},[l]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{c({type:2,activation:s})},[s]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{typeof n=="function"&&(b.current=n)},[n]),(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{if(u.tabs.length<=0||i===null&&u.selectedIndex!==null)return;let p=u.tabs.map(P=>P.current).filter(Boolean),f=p.filter(P=>!P.hasAttribute("disabled")),d=i!=null?i:t;if(d<0)c({type:0,index:p.indexOf(f[0])});else if(d>u.tabs.length)c({type:0,index:p.indexOf(f[f.length-1])});else{let P=p.slice(0,d),R=[...p.slice(d),...P].find(g=>f.includes(g));if(!R)return;c({type:0,index:p.indexOf(R)})}},[t,i,u.tabs,u.selectedIndex]);let T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(u.selectedIndex);(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{T.current=u.selectedIndex},[u.selectedIndex]);let y=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>[u,{dispatch:c,change(p){T.current!==p&&b.current(p),T.current=p,c({type:0,index:p})}}],[u,c]);return react__WEBPACK_IMPORTED_MODULE_0__.createElement(zt.Provider,{value:y},E({props:{...a},slot:m,defaultTag:dl,name:"Tabs"}))}var fl="div";function ml(e){let[{selectedIndex:t,orientation:r}]=Le("Tab.List"),o={selectedIndex:t};return E({props:{...e,...{role:"tablist","aria-orientation":r}},slot:o,defaultTag:fl,name:"Tabs.List"})}var bl="button";function De(e){var C,R;let t=`headlessui-tabs-tab-${A()}`,[{selectedIndex:r,tabs:o,panels:n,orientation:i,activation:a},{dispatch:l,change:s}]=Le(De.name),u=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),c=I(u,g=>{!g||l({type:7})});x(()=>(l({type:3,tab:u}),()=>l({type:4,tab:u})),[l,u]);let m=o.indexOf(u),b=m===r,T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(g=>{let v=o.map(h=>h.current).filter(Boolean);if(g.key===" "||g.key==="Enter"){g.preventDefault(),g.stopPropagation(),s(m);return}switch(g.key){case"Home":case"PageUp":return g.preventDefault(),g.stopPropagation(),M(v,1);case"End":case"PageDown":return g.preventDefault(),g.stopPropagation(),M(v,8)}return S(i,{vertical(){if(g.key==="ArrowUp")return M(v,2|16);if(g.key==="ArrowDown")return M(v,4|16)},horizontal(){if(g.key==="ArrowLeft")return M(v,2|16);if(g.key==="ArrowRight")return M(v,4|16)}})},[o,i,m,s]),y=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{var g;(g=u.current)==null||g.focus()},[u]),p=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(()=>{var g;(g=u.current)==null||g.focus(),s(m)},[s,m,u]),f=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({selected:b}),[b]),d={ref:c,onKeyDown:T,onFocus:a==="manual"?y:p,onClick:p,id:t,role:"tab",type:U(e,u),"aria-controls":(R=(C=n[m])==null?void 0:C.current)==null?void 0:R.id,"aria-selected":b,tabIndex:b?0:-1};return E({props:{...e,...d},slot:f,defaultTag:bl,name:"Tabs.Tab"})}var Tl="div";function yl(e){let[{selectedIndex:t}]=Le("Tab.Panels"),r=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({selectedIndex:t}),[t]);return E({props:e,slot:r,defaultTag:Tl,name:"Tabs.Panels"})}var gl="div",Pl=1|2;function xl(e){var T,y;let[{selectedIndex:t,tabs:r,panels:o},{dispatch:n}]=Le("Tab.Panel"),i=`headlessui-tabs-panel-${A()}`,a=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),l=I(a,p=>{!p||n({type:7})});x(()=>(n({type:5,panel:a}),()=>n({type:6,panel:a})),[n,a]);let s=o.indexOf(a),u=s===t,c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({selected:u}),[u]),m={ref:l,id:i,role:"tabpanel","aria-labelledby":(y=(T=r[s])==null?void 0:T.current)==null?void 0:y.id,tabIndex:u?0:-1};return E({props:{...e,...m},slot:c,defaultTag:gl,features:Pl,visible:u,name:"Tabs.Panel"})}De.Group=cl;De.List=ml;De.Panels=yl;De.Panel=xl;function Bo(){let e=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(!0);return (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{e.current=!1},[]),e.current}function No(e){let t={called:!1};return(...r)=>{if(!t.called)return t.called=!0,e(...r)}}function Yt(e,...t){e&&t.length>0&&e.classList.add(...t)}function ut(e,...t){e&&t.length>0&&e.classList.remove(...t)}function El(e,t){let r=k();if(!e)return r.dispose;let{transitionDuration:o,transitionDelay:n}=getComputedStyle(e),[i,a]=[o,n].map(l=>{let[s=0]=l.split(",").filter(Boolean).map(u=>u.includes("ms")?parseFloat(u):parseFloat(u)*1e3).sort((u,c)=>c-u);return s});return i!==0?r.setTimeout(()=>{t("finished")},i+a):t("finished"),r.add(()=>t("cancelled")),r.dispose}function Xt(e,t,r,o,n,i){let a=k(),l=i!==void 0?No(i):()=>{};return ut(e,...n),Yt(e,...t,...r),a.nextFrame(()=>{ut(e,...r),Yt(e,...o),a.add(El(e,s=>(ut(e,...o,...t),Yt(e,...n),l(s))))}),a.add(()=>ut(e,...t,...r,...o,...n)),a.add(()=>l("cancelled")),a.dispose}function le(e=""){return (0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>e.split(" ").filter(t=>t.trim().length>1),[e])}var dt=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);dt.displayName="TransitionContext";function Cl(){let e=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(dt);if(e===null)throw new Error("A <Transition.Child /> is used but it is missing a parent <Transition /> or <Transition.Root />.");return e}function Sl(){let e=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(ct);if(e===null)throw new Error("A <Transition.Child /> is used but it is missing a parent <Transition /> or <Transition.Root />.");return e}var ct=(0,react__WEBPACK_IMPORTED_MODULE_0__.createContext)(null);ct.displayName="NestingContext";function ft(e){return"children"in e?ft(e.children):e.current.filter(({state:t})=>t==="visible").length>0}function $o(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(e),r=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)([]),o=Be();(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{t.current=e},[e]);let n=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)((a,l=1)=>{var u;let s=r.current.findIndex(({id:c})=>c===a);s!==-1&&(S(l,{[0](){r.current.splice(s,1)},[1](){r.current[s].state="hidden"}}),!ft(r)&&o.current&&((u=t.current)==null||u.call(t)))},[t,o,r]),i=(0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(a=>{let l=r.current.find(({id:s})=>s===a);return l?l.state!=="visible"&&(l.state="visible"):r.current.push({id:a,state:"visible"}),()=>n(a,0)},[r,n]);return (0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({children:r,register:i,unregister:n}),[i,n,r])}function Al(){}var hl=["beforeEnter","afterEnter","beforeLeave","afterLeave"];function Qo(e){var r;let t={};for(let o of hl)t[o]=(r=e[o])!=null?r:Al;return t}function Ol(e){let t=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(Qo(e));return (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{t.current=Qo(e)},[e]),t}var Il="div",qo=1;function zo(e){let{beforeEnter:t,afterEnter:r,beforeLeave:o,afterLeave:n,enter:i,enterFrom:a,enterTo:l,entered:s,leave:u,leaveFrom:c,leaveTo:m,...b}=e,T=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null),[y,p]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)("visible"),f=b.unmount?0:1,{show:d,appear:P,initial:C}=Cl(),{register:R,unregister:g}=Sl(),v=A(),h=(0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(!1),O=$o(()=>{h.current||(p("hidden"),g(v),X.current.afterLeave())});x(()=>{if(!!v)return R(v)},[R,v]),x(()=>{if(f===1&&!!v){if(d&&y!=="visible"){p("visible");return}S(y,{hidden:()=>g(v),visible:()=>R(v)})}},[y,v,R,g,d,f]);let L=le(i),N=le(a),K=le(l),V=le(s),Fe=le(u),ge=le(c),we=le(m),X=Ol({beforeEnter:t,afterEnter:r,beforeLeave:o,afterLeave:n}),F=q();(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{if(F&&y==="visible"&&T.current===null)throw new Error("Did you forget to passthrough the `ref` to the actual DOM node?")},[T,y,F]);let $=C&&!P;x(()=>{let bt=T.current;if(!!bt&&!$)return h.current=!0,d&&X.current.beforeEnter(),d||X.current.beforeLeave(),d?Xt(bt,L,N,K,V,Tt=>{h.current=!1,Tt==="finished"&&X.current.afterEnter()}):Xt(bt,Fe,ge,we,V,Tt=>{h.current=!1,Tt==="finished"&&(ft(O)||(p("hidden"),g(v),X.current.afterLeave()))})},[X,v,h,g,O,T,$,d,L,N,K,Fe,ge,we]);let H={ref:T},Pe=b;return react__WEBPACK_IMPORTED_MODULE_0__.createElement(ct.Provider,{value:O},react__WEBPACK_IMPORTED_MODULE_0__.createElement(W,{value:S(y,{visible:0,hidden:1})},E({props:{...Pe,...H},defaultTag:Il,features:qo,visible:y==="visible",name:"Transition.Child"})))}function mt(e){let{show:t,appear:r=!1,unmount:o,...n}=e,i=_();if(t===void 0&&i!==null&&(t=S(i,{[0]:!0,[1]:!1})),![!0,!1].includes(t))throw new Error("A <Transition /> is used but it is missing a `show={true | false}` prop.");let[a,l]=(0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(t?"visible":"hidden"),s=$o(()=>{l("hidden")}),u=Bo(),c=(0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(()=>({show:t,appear:r||!u,initial:u}),[t,r,u]);(0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(()=>{t?l("visible"):ft(s)||l("hidden")},[t,s]);let m={unmount:o};return react__WEBPACK_IMPORTED_MODULE_0__.createElement(ct.Provider,{value:s},react__WEBPACK_IMPORTED_MODULE_0__.createElement(dt.Provider,{value:c},E({props:{...m,as:react__WEBPACK_IMPORTED_MODULE_0__.Fragment,children:react__WEBPACK_IMPORTED_MODULE_0__.createElement(zo,{...m,...n})},defaultTag:react__WEBPACK_IMPORTED_MODULE_0__.Fragment,features:qo,visible:a==="visible",name:"Transition"})))}mt.Child=function(t){let r=(0,react__WEBPACK_IMPORTED_MODULE_0__.useContext)(dt)!==null,o=_()!==null;return!r&&o?react__WEBPACK_IMPORTED_MODULE_0__.createElement(mt,{...t}):react__WEBPACK_IMPORTED_MODULE_0__.createElement(zo,{...t})};mt.Root=mt;


/***/ })

}]);