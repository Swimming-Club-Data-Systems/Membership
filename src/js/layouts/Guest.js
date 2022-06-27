import React from "react";
import ApplicationLogo from "@/Components/ApplicationLogo";
import { Link } from "react-router-dom";
import Card from "@/Components/Card";
import Container from "@/Components/Container";

export default function Guest({ children, cardHeader, cardFooter }) {
  return (
    <div className="bg-gray-100">
      <Container>
        <div className="grid min-h-screen content-center">
          <div>
            <div className="mb-4 text-center">
              <Link to="/">
                <ApplicationLogo className="h-20 w-20 fill-current text-gray-500" />
              </Link>
            </div>

            <Card>{children}</Card>
          </div>
        </div>
      </Container>
    </div>
  );
}
