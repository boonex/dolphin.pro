Step by step
------------

For Drupal 7.x

1. Install WYSIWYG module, with TINYMCE as the editor, make sure this works first.
2. Place moxiemanager folder inside your /sites/all/modules folder.
3. Copy moxiemanager/install/config.template.php into /moxiemanager/config.php file (remove install folder).
4. Open config.php, add your license key from your account, then change the authenticator to "DrupalAuthenticator".
5. Go into Drupal -> Modules, scroll to bottom and enable the MoxieManager module.
6. Go into WYSIWYG module and configure buttons, add the "Insert file" button, should be at the bottom.
7. Open up a page and start to edit it with TinyMCE, a new button should show up, and you should have a browse button inside the image/link dialog.

Optional: You might want to open config.php and change the "filesystem.rootpath" to where you want to store images, absolute or relavive path on disk.

If anything fails, just go through everything and check so you made it all correctly, if you still have problems, open a support ticket.