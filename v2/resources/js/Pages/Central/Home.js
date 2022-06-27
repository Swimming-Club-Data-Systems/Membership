import React from "react";
import { Head } from "@inertiajs/inertia-react";
import Link from "@/Components/Link";
import ApplicationLogo from "@/Components/ApplicationLogo";
import Container from "@/Components/Container";
import Alert from "@/Components/Alert";

export default function Home(props) {
  return (
    <>
      <Head>
        <title>Membership Software for Swimming Clubs</title>
        <meta
          name="description"
          content="Membership Software for Swimming Clubs by SCDS"
        />
      </Head>

      <div className="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-gray-100 dark:from-indigo-900 dark:via-purple-900 dark:to-pink-900">
        <div className="mx-auto py-3">
          <Container>
            <div className="mb-3">
              <ApplicationLogo />
              <h1 className="mt-2 text-3xl font-bold text-indigo-100 dark:text-indigo-400">
                Swimming Club Data Systems
              </h1>
              <p className="text-2xl font-bold">
                Membership software for swimming clubs
              </p>
            </div>
          </Container>
        </div>

        <Container>
          <div className="mb-3 pb-3">
            <p className="text-xl">
              Manage your members, subscriptions, competition entries and more.
            </p>
            <p>
              For sales enquiries, please contact{" "}
              <a
                className="text-indigo-100 dark:text-indigo-400"
                href="mailto:sales@myswimmingclub.uk"
              >
                sales@myswimmingclub.uk
              </a>
              .
            </p>
          </div>
        </Container>
      </div>

      <Container>
        <Alert variant="warning" title="Attention">
          This is a development build of the new Membership System. It is not
          for production use.
        </Alert>
      </Container>

      <Container>
        <p className="mb-3">
          This is Laravel v{props.laravelVersion} (PHP v{props.phpVersion})
        </p>

        <div className="mb-3">
          <Link href={route("login")}>Admin log in</Link>
        </div>
      </Container>
    </>
  );
}
