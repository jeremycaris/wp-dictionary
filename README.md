# Dictionary
A utility plugin for Wordpress that adds a dictionary post type, creates default categories A-Z if they don't already exist, modifies searches to only match whole words or exact searches, enforces full post display on dictionary category pages, sorts the posts by title instead of date, and adds a menu for entries which is available via the shortcode [dictionary_menu]. Only categories that are not empty will show in the menu.

&nbsp;
## How To Add Dictionary Entries
To add dictionary entries, in the admin area, simply click on **Add New** under the new **Dictionary** post type.

<p align="center"><img src="https://714web.com/wp-content/uploads/2018/01/dictionary-entries.png" width="400" /></p>

&nbsp;
## How To Add The Dictionary Menu
There are two ways to the add dictionary menu. The first is to add the shortcode **[dictionary_menu]** to a widget area and then display that widget in the dictionary category and pages. The second is to insert **generate_dictionary_menu();** into your theme's archive template file.


<p align="center"><img src="https://714web.com/wp-content/uploads/2018/01/theme-edit.png" width="500" /></p>

&nbsp;
## How To View Dictionary
To view your dictionary, just browse to the newly created category: your-website-url/dictionary/

![dictionary example](https://714web.com/wp-content/uploads/2018/01/dictionary-example.png)

&nbsp;
## How To Add Dictionary To The Navigational Menu
To add the dictionary category to your navigational menu, first go to **Appearances** > **Menus** and then click on **Custom Links**, and then add:

URL: **your-website-url/dictionary/** (Or keep it relative: /dictionary/)

Link Text: **Dictionary**

<p align="center"><img src="http://714web.com/wp-content/uploads/2018/01/custom-link.png" width="400" /></p>

And don't forget to save your menu update :)
