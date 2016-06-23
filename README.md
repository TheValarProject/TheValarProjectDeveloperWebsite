# The Valar Project Developer Website
This is a sandbox for The Valar Project's web developers. Below are detailed instructions on how develop with The Valar Project. If you are looking for the official code for our website, please go [here](https://github.com/TheValarProject/TheValarProjectWebsite).

## Starting off
These instructions assume you have a basic understanding of git. If you do not, take a moment to learn about it before continuing. [Pro Git](https://git-scm.com/book/en/v2) is a good reference.

To make it easy for TVP web developers to test their code, we have created this repository. To begin, you need to have write access to it. To get this, please make an issue titled `Access Request (<your_github_username>)`, obviously replacing <your_github_username> with your actual Github account username. In the body of the issue, provide a brief description of what you want to do with sandbox access.

Within a few days, a project administrator will add you to the Website Experimenters team, and close the issue you filed.

## Making changes
There are only certain branches that you will be allowed to modify. Modifying branches without proper permission may result in loosing contributor access. Let's take a moment to go over the types of branches:

#### Master branch
The master branch contains help files for contributing and stores some configuration data needed for the automated deployment system. Under most circumstances you should not need to edit this branch.

#### Mirror branch
The mirror branch contains a complete copy of the actual website code. This is provided for convenience when making a new branch. Never commit changes to this branch.

#### User branches
User branches allow access only to a single specific user. The follow naming convention is used: `u-<id_number>-<username>` where <id_number> is a number to uniquely identify the branch from other branches by the same user and <username> is the Github username of the only user that can access this branch. You can only modify these if your username matches the username in the branch name. You may make as many of these as you need for yourself.

#### Group branches
Group branches allow access only to a specific subset of users. The following naming convention is used: `g-<id_number/project_name>` where <id_number/project_name> can be a uniquely identifying number, or a short name to quickly describe what the branch is for. We haven't settled on a way of setting this up yet, so for now file an issue titled `Group Request`. In the body of the issue specify what you want this group for and who you want to be in this group. Inside any group branch there should be a `group-info.json` file, which will contain information on who is allowed to edit it. If you are not in this list, you cannot edit the branch. To delete the branch, you should have consent of all members of the group.

#### Public branches
Public branches allow access to anyone. We highly suggest you use public branches for any major projects (such as new features) so that others can help you if they feel up to it. The following naming convention is used: `p-<id_number/project_name>` where <id_number/project_name> is used just like it is in the group branch. Anyone can make a new public branch, and anyone can edit it. Please do not delete public branches. If the project it was aimed for is complete or something like that, please file an issue titled `Branch Deletion Request (<branch_name>)` and describe in the body of the issue why it should be deleted.

## Viewing your changes
To view your changes, head to http://developer.tvp.elementfx.com. This is the homepage for TVP website development. Here you will be able to change the active branch. What this means is that when you change it to a branch, you can go to http://tvp.elementfx.com, and it will transparently redirect to the code in your branch (updated about every 5 minutes). This means that you do not have to change the links to point to your specific branch location. Our goal is to have it behave exactly the same as if your changes were merged into the main website.

## Database access
For security reasons, we obviously cannot allow you to access the actual database. We are however working on making a new database specifically for developer access that will mirror the structure of the original database and will have many fake entries to aid with testing. We will update this section once we get it all set up.

## Submitting your changes
When you believe your changes are ready to be included in the website, you must make a pull request to the the main website code repository at https://github.com/TheValarProject/TheValarProjectWebsite. To do this, first fork the repository, then add that repository as remote for your existing local developer repository. Then you can push your branch to the fork. From there, all that's left to do is make a pull request. A website administrator will merge the code either into the next release or directly to the site, depending on the urgency of the changes. If merged directly, you should see the changes reflected on the website shortly after merging. If merged into the next version, you will have to wait for the next version to be released to see your changes.