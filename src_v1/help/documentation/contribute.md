# Contributing

This guide assumes you already know how to use *Git* and *GitHub.com*. It also assumes you know what a git *branch* is and what a *Pull Request*/*Merge Request* is in services like GitHub and GitLab. If you don't know these, please read up on them before you continue.

## How to make a contribution

1. Decide on the document you would like to create or edit
  * All help and support documentation is located in `./src/help`
2. Create a new branch
  * If you're not a member of this project, you'll need to fork the project to do this
3. Make and *commit* your changes
4. Create a *Pull Request* on GitHub.com
  * Select `development-main` as the *base*
  * Select `<YOUR BRANCH NAME>` as the *source/compare* branch
5. Create the pull request with a description which explains
  * What you have changed
  * Why you have changed it

Pull Requests will be merged into `development-main`, which is the staging branch. This branch is then periodically merged into `main`.

## Managing a Table of Contents

A table of contents provides a useful mechanism for users to find their way around a particular topic, but providing and editing a table of contents is a little more involved than just editing a markdown file.

Here's how it works;

* A table of contents is stored as a `YAML` file called `toc.yml`
* When a user loads a page, we look up the directory tree. The first `toc.yml` file we find is selected
  * We only look as far as `./src/help` or 15 enclosing folders up the tree, whichever comes first

A table of contents YAML file looks like this;

```yaml
- name: Windows Terminal
  items: 
    - name: Overview
      href: index.md
    - name: Get started
      href: get-started.md
    - name: Customize settings
      items:
        - name: Startup
          href: customize-settings/startup.md
        - name: Interaction
          href: customize-settings/interaction.md
        - name: Appearance
          href: customize-settings/appearance.md
        - name: Color schemes
          href: customize-settings/color-schemes.md
        - name: Rendering
          href: customize-settings/rendering.md
        - name: Profile - General
          href: customize-settings/profile-general.md
        - name: Profile - Appearance
          href: customize-settings/profile-appearance.md
        - name: Profile - Advanced
          href: customize-settings/profile-advanced.md
        - name: Actions
          href: customize-settings/actions.md
```

This example comes from Microsoft, who use the same structure for their documentation platform.

There is no enforced limit for the depth of this tree however `YAML` files must always be valid.

All links in a table of contents are relative but you may not make a link to a location which is higher up in the directory tree than the directory containing the `toc.yml` file.

## Managing breadcrumbs

Breadcrumbs are still a work in progress.