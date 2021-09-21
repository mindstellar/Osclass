# Osclass 5.1.0 Release Notes {#release-notes-5-1-0}
* New and improved backend
* New backend is based on Bootstrap 5
* Many changes are made to make it more user friendly on small screens, while keeping the same functionality
* New system info page in tools menu.
* New form input classe is introduced, which will be the new default for all form elements, soon new API will be introduced to use it.
* New translations, from mindstellar/i10n-osclass
* New versions of translation can be download from our repositories in Osclass backend
* Now language have info for text direction, which can be utilized by developers
* New translations for new languages
* Improvement in osclass upgrade experience
* Less dependecy on JQuery-UI and other libraries, goal is to remove them completely with native javascript or use bootstrap components
* Major rewrite of old Jquery based code to Pure JS (Still lots to do)
* Lots of improvement of Escape, Sanitize classes and they are used now in many places
* Many security flaws are fixed, thanks to new classes
* Huge improvement in locations stats generation, on average a 10 x improvement in performance
* Many fixes which improve compatibility with older plugins but not for ones which use ancient DB access methods.
* Huge reductions in PHP Notices and warnings with newer PHP versions
* Overall performance improvements and code refactoring to make it more stable and easier to maintain
* Now styles can be registered like you have registered scripts.
* JS scripts can be enqueued in middle of view and will be executed in the end of the page.
* You can enable the alllow prerelease setting in user dashboard to get new features and bugfixes and continue to test this version. 
* over 250 commits since the last release

There is lot more under the hood changes, which are not listed here, but you can see them in the commit log.

Backend rewrite was a lot of work, but now it is finally done and it is much more user friendly. Many new componets/API will be introduced in the future. So, if you are a developer, that'll make you happy. It was a maratthan job since last two-three months and I am very happy with the result. I hope you will enjoy it. If you wanna help or donate to support the project? Just buy me few coffee at https://www.paypal.com/paypalme/navjottomer, it will be great :)

Source: https://github.com/mindstellar/Osclass