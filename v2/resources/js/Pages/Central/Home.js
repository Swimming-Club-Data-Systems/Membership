import React from 'react';
import { Link, Head } from '@inertiajs/inertia-react';
import ApplicationLogo from '@/Components/ApplicationLogo';

export default function Home(props) {
  return (
    <>
      <Head>
        <title>Membership Software for Swimming Clubs</title>
        <meta name="description" content="Membership Software for Swimming Clubs by SCDS" />
      </Head>

      <div className="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-900 dark:via-purple-900 dark:to-pink-900 text-gray-100">
        <div className="container mx-auto py-3 h-screen">
          <div className="mb-3">
            <ApplicationLogo />
            <h1 className="mt-2 text-indigo-100 dark:text-indigo-400 font-bold text-3xl">Swimming Club Data Systems</h1>
            <p className="font-bold text-2xl">Membership software for swimming clubs</p>
          </div>

          <div className="mb-3">
            <p className="text-xl">Manage your members, subscriptions, competition entries and more.</p>
            <p>For sales enquiries, please contact <a className="text-indigo-100 dark:text-indigo-400" href="mailto:sales@myswimmingclub.uk">sales@myswimmingclub.uk</a>.</p>
          </div>

          <div className="mb-3">
            <Link href={route('login')} className="text-indigo-100 dark:text-indigo-400">
              Admin log in
            </Link>
          </div>
        </div>
      </div>

      <div className="bg-indigo-600 dark:bg-indigo-800 text-white">
        <div className="container mx-auto py-3">
          <p>
            Laravel v{props.laravelVersion} (PHP v{props.phpVersion})
          </p>
        </div>
      </div>
    </>
  );
}
