import React from "react";
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/react/solid";
import { Link } from "@inertiajs/inertia-react";

const Pagination = ({ collection }) => {
  const {
    current_page,
    from,
    to,
    total,
    links,
    first_page_url,
    last_page_url,
    prev_page_url,
    next_page_url,
  } = collection;

  const displayLinks = links.map((link, idx) => {
    const linkClass = link.active
      ? "z-10 bg-indigo-50 border-indigo-500 text-indigo-600"
      : "bg-white border-gray-300 text-gray-500 hover:bg-gray-50";
    const disabled = link.url ? "" : "bg-gray-50 pointer-events-none";
    const aria = link.active ? { "aria-current": "page" } : {};

    let label = link.label;

    switch (link.label) {
      case "&laquo; Previous":
        label = (
          <>
            <span className="sr-only">Previous</span>
            <ChevronLeftIcon className="h-5 w-5" aria-hidden="true" />
          </>
        );
        break;
      case "Next &raquo;":
        label = (
          <>
            <span className="sr-only">Next</span>
            <ChevronRightIcon className="h-5 w-5" aria-hidden="true" />
          </>
        );
        break;
      default:
        break;
    }

    return (
      <Link
        key={idx}
        href={link.url}
        disabled={!link.url}
        {...aria}
        className={`relative inline-flex items-center border px-4 py-2 text-sm font-medium first-of-type:rounded-l-md last-of-type:rounded-r-md ${linkClass} ${disabled}`}
      >
        {label}
      </Link>
    );
  });

  return (
    <div className="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
      <div className="flex flex-1 justify-between sm:hidden">
        {prev_page_url && (
          <Link
            href={prev_page_url}
            className="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Previous
          </Link>
        )}
        {next_page_url && (
          <Link
            href={next_page_url}
            className="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Next
          </Link>
        )}
      </div>
      <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p className="text-sm text-gray-700">
            Showing <span className="font-medium">{from}</span> to{" "}
            <span className="font-medium">{to}</span> of{" "}
            <span className="font-medium">{total}</span> results
          </p>
        </div>
        <div>
          <nav
            className="relative z-0 inline-flex -space-x-px rounded-md shadow-sm"
            aria-label="Pagination"
          >
            {displayLinks}
          </nav>
        </div>
      </div>
    </div>
  );
};

export default Pagination;