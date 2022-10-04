# Contributing

This guide assumes you already know how to use *Git* and *GitHub.com*. It also assumes you know what a git *branch* is and what a *Pull Request*/*Merge Request* is in services like GitHub and GitLab. If you don't know these, please read up on them before you continue.

## How to make a contribution

1. Decide on the document you would like to create or edit
  * All help and support documentation is located in `./src/docs/`
2. Create a new branch
  * If you're not a member of this project, you'll need to fork the project instead
3. Make and *commit* your changes
4. Create a *Pull Request* on GitHub.com
  * Select `development-main` as the *base*
  * Select `<YOUR BRANCH NAME>` as the *source/compare* branch
5. Create the pull request with a description which explains
  * What you have changed
  * Why you have changed it

Pull Requests will be merged into `development-main`, which is the staging branch. This branch is then periodically merged into `main`.

## Understanding Docusaurus