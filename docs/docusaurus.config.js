// @ts-check
// Note: type annotations allow type checking and IDEs autocompletion

const lightCodeTheme = require('prism-react-renderer/themes/github');
const darkCodeTheme = require('prism-react-renderer/themes/dracula');

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'SCDS Membership',
  tagline: 'Membership management software for swimming clubs',
  url: 'https://docs.myswimmingclub.uk',
  baseUrl: '/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',

  // GitHub pages deployment config.
  // If you aren't using GitHub pages, you don't need these.
  organizationName: 'Swimming-Club-Data-Systems', // Usually your GitHub org/user name.
  projectName: 'Membership', // Usually your repo name.

  // Even if you don't use internalization, you can use this field to set useful
  // metadata like html lang. For example, if your site is Chinese, you may want
  // to replace "en" with "zh-Hans".
  i18n: {
    defaultLocale: 'en-GB',
    locales: ['en-GB'],
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          editUrl:
            'https://github.com/Swimming-Club-Data-Systems/Membership/tree/main/docs/',
        },
        blog: {
          showReadingTime: true,
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          // editUrl:
            // 'https://github.com/facebook/docusaurus/tree/main/packages/create-docusaurus/templates/shared/',
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      navbar: {
        title: 'SCDS Membership',
        logo: {
          alt: 'SCDS Logo',
          src: 'img/logo.svg',
        },
        items: [
          {
            type: 'doc',
            docId: 'index',
            position: 'left',
            label: 'Docs',
          },
          // {to: '/blog', label: 'Blog', position: 'left'},
          {
            href: 'https://github.com/Swimming-Club-Data-Systems/Membership',
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Help and Support',
            items: [
              {
                label: 'Documentation',
                to: '/docs',
              },
              {
                label: 'Report mail abuse',
                href: 'https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u',
              },
              // {
              //   label: 'What\s new?',
              //   to: '/docs/intro',
              // },
            ],
          },
          {
            title: 'Organisation',
            items: [
              {
                label: 'Admin Login',
                href: 'https://myswimmingclub.uk/admin',
              },
              {
                label: 'About Us',
                href: 'https://myswimmingclub.uk',
              },
              {
                label: 'Carbon Removal',
                href: 'https://climate.stripe.com/pkIT9H',
              },
              {
                label: 'GitHub',
                href: 'https://github.com/Swimming-Club-Data-Systems/Membership',
              },
            ],
          },
          {
            title: 'Related Sites',
            items: [
              {
                label: 'British Swimming',
                href: 'https://www.britishswimming.org/',
              },
              {
                label: 'Swim England',
                href: 'https://www.swimming.org/swimengland/',
              },
              {
                label: 'swimming.org',
                href: 'https://www.swimming.org/',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} Swimming Club Data Systems.`,
      },
      prism: {
        theme: lightCodeTheme,
        darkTheme: darkCodeTheme,
        additionalLanguages: ['csharp', 'php', 'dns-zone-file'],
      },
    }),
};

module.exports = config;
