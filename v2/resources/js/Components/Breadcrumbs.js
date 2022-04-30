import React from "react";
import { HomeIcon, ChevronRightIcon } from "@heroicons/react/solid";
import { Link as InertiaLink, usePage } from "@inertiajs/inertia-react";

const Link = (props) => {
  return (
    <InertiaLink
      {...props}
      className="text-gray-700 hover:text-gray-800"
    />
  )
}

const Breadcrumbs = (props) => {
  let itemList = [];

  const { url } = usePage();

  itemList.push("home");

  if (props.crumbs) {
    props.crumbs.forEach((item) => {
      itemList.push("separator");
      itemList.push(item);
    });
  }

  const crumbs = itemList.map((item, idx) => {
    let render;
    switch (item) {
      case "separator":
        render = <ChevronRightIcon className="h-4" />;
        break;
      case "home":
        render = (
          <Link href="/">
            <HomeIcon className="h-4" />
          </Link>
        );
        break; 
      default:
        if (item.href === url || item.href === window.location.href) {
          render = <span aria-current="page" className="pointer-events-none">{item.name}</span>;
        } else {
          render = <Link href={item.href}>{item.name}</Link>;
        }
        break;
    }

    return <li key={idx}>{render}</li>;
  });

  if (props.crumbs) {
    return (
      <nav aria-label="breadcrumb">
        <ol className="mb-3 flex items-center space-x-2 text-gray-700">
          {crumbs}
        </ol>
      </nav>
    );
  }
  return null;
};

export default Breadcrumbs;
