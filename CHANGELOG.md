## Update changelog for Osclass 5.1.0 Release Notes {#release-notes-5-1-0}
* New: Backend is based on Bootstrap 5
* New: Many changes are made to make it more user-friendly on small screens while keeping the same functionality
* New: System info page in the tools menu.
* New: form input class is introduced, which will be the new default for all form elements, soon a new API will be introduced to use it.
* New: Translations, from mindstellar/i10n-osclass
* New: Versions of translation can be downloaded from our repositories in the Osclass backend
* New: Language has info for text direction, which can be utilized by developers
* New: Translations for new languages
* New: Translated custom fields. They can be translated into the custom-field editor.
* New: open-in-new-tab option for URL type custom fields.
* New: Login as a user feature. Can be used to log in as another user. You'll find this option on the user edit page. Thanks, @dftd
* New: You can enable the allow-prerelease setting in the user dashboard to get new features and bug fixes and continue to test this version.
* Improvement: In osclass upgrade experience
* Improvement: Less dependency on JQuery-UI and other libraries, the goal is to remove them completely with native javascript or use bootstrap components
* Improvement: Major rewrite of old Jquery based code to Pure JS (Still lots to do)
* Improvement: Escape, Sanitize classes, and they are used now in many places
* Security: Many security flaws are fixed, thanks to new classes
* Improvement: Locations stats generation, on average a 10 x improvement in performance
* Improvement: Compatibility with older plugins but not for ones that use ancient DB access methods.
* Improvement: Huge reductions in PHP Notices and warnings with newer PHP versions
* Improvement: Now styles can be registered like you have registered scripts.
* Improvement: JS scripts can be enqueued in the middle of view and will be executed at the end of the page.
* Speed: Overall performance improvements and code refactoring to make it more stable and easier to maintain
* Speed: Database improvements, from 10x to 5x faster in some cases, reduced the query footprint in many cases
* Changed: Default DB engine from MyISAM to InnoDB for t_item_description table.
* Fixed: JS enqueued in the footer was not working in some cases.
* Fixed: Multilingual issue with the backend.
* Fixed: #400: Issues storing ipv6 addresses in a few places.
* Fixed: Fresh installation of Osclass does not work.
* Removed: the google-map plugin. as functionality is now provided by the core.
  over 300 + commits since the last release
  There is a lot more under the hood changes, which are not listed here, but you can see them in the commit log.

Backend rewrite was a lot of work, but now it is finally done, and it is much more user-friendly. Many new components/APIs will be introduced in the future. So, if you are a developer, that'll make you happy. It was a marathon job for the last two to three months, and I am very happy with the result. I hope you will enjoy it. If you want to help or donate to support the project? Just buy me little coffee at https://www.paypal.com/paypalme/navjottomer, it will be great :)

Source: https://github.com/mindstellar/Osclass