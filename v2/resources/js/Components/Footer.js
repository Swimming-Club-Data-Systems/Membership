import { Link } from "@inertiajs/inertia-react";
import React from "react";
import ApplicationLogo from "./ApplicationLogo";
import Container from "./Container";

const Footer = (props) => {
  return (
    <div className="bg-gray-200 text-gray-700  dark:bg-slate-900 dark:text-slate-200">
      <Container>
        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-16 border-b border-gray-300 dark:border-slate-200 py-5">
          <div className="md:col-span-3 lg:col-span-2">
            <ApplicationLogo />

            <p className="pt-5">
              Helping swimming clubs across the UK run efficiently.
            </p>
          </div>
          <div className="space-y-4">
            <p className="text-gray-600 dark:text-slate-300 font-semibold">Help and Support</p>
            <div>
              <Link>Documentation</Link>
            </div>

            <div>
              <Link>Report mail abuse</Link>
            </div>

            <div>
              <Link>What's new?</Link>
            </div>
          </div>

          <div className="space-y-4">
            <p className="text-gray-600 dark:text-slate-300 font-semibold">Organisation</p>
            <div>
              <Link>Admin Login</Link>
            </div>

            <div>
              <Link>About Us</Link>
            </div>

            <div>
              <a href="https://climate.stripe.com/pkIT9H">Carbon Removal</a>
            </div>

            <div>
              <a href="https://github.com/Swimming-Club-Data-Systems" target="_blank">GitHub</a>
            </div>
          </div>

          <div className="space-y-4">
            <p className="text-gray-600 dark:text-slate-300 font-semibold">Related Sites</p>
            <div>
              <a href="https://www.britishswimming.org/" target="_blank">British Swimming</a>
            </div>

            <div>
              <a href="https://www.swimming.org/swimengland/" target="_blank">Swim England</a>
            </div>

            <div>
              <a href="https://www.swimming.org/swimengland/" target="_blank">swimming.org</a>
            </div>
          </div>
        </div>
        <p className="py-5 text-center font-semibold text-gray-500 dark:text-slate-400 text-sm">
          &copy; Swimming Club Data Systems 2022
        </p>
      </Container>
    </div>
  )
}

export default Footer;