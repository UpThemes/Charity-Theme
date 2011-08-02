# Carrington Build

## What is Carrington Build

Carrington Build is a drop in addition to the standard WordPress post and page edit screens. Carrington Build allows you to construct complex page layouts without having to know HTML or CSS. Layouts are based on the concept of breaking up the page content in to blocks of content that are arranged by row.

---


## Getting Started

- Start in WordPress
- Switch to Carrington Build
- Auto draft save
- Select build or template
- Content saves are immediate
- Editing other post properties works as normal
- Draft, publish & publish date all work as normal

---


## Rows

Carrington Build can add multiple rows of data to a post. Each row contains blocks. Different row types contain different block layouts. 

**To add a row to a layout:**

- Click on "Add row" in the Post Edit screen in the WordPress admin. If there are rows already in the layout then "Add row" will be at the bottom of that list of rows.
- Select a row type to add.
- The row will be added to the layout

**To remove a row from a layout:**

- Click the "(x)" at the top-left of the row box.
- A confirmation dialog box will appear
	- Click "Delete Row" to remove the row or "Cancel" to cancel.
- When a row is deleted all the content entered in any of its modules is deleted permanently as well.

**To reorder rows:**

- Drag the row by its "grabber", the dark shaded area along the left hand edge of the row, and drag it to its new position. The cursor will change when you enter this area to indicate that you can move the row.

### Default Rows

- Single Column
- Double Column
	- 50/50 split
	- 66/33 split
	- 33/66 split
	- column 1 float left
		- allows longer content on the right to flow under the item on the left
	- column 2 float right
		- allows longer content on the right to flow under the item on the right
- Triple Column
	- 33/33/33 split

---


## Modules

Carrington Build's rows contain blocks. Each block can contain a single module.

**To add a module to a block:**

- Click on "Add Module" at the bottom of the empty block
- In the popup box select the module type you'd like to add.
	- **Hint:** Use the toggle in the bottom right to switch between Icon and List view.
- Edit the module form when it loads.
- Click "Save" to save the text. Nothing is added to the post until "Save" is clicked.
- or Click "Cancel" to abort adding the module.
- **Hint:** Multiple modules can be added to the same block.

**To delete a module from a block:**

- Click "Delete" in the Module that you'd like to delete.
- A confirmation dialog box will appear.
	- Click "Delete Module" to remove the module or "Cancel" to cancel.
- When the module is deleted all its content is removed permanently.

**To edit a module:**

- Click "Edit" in the module to be edited to bring up the edit dialog.
- Modify the content as desired and click "Save" to commit the changes.

**To reorder a module:**

- When a row block contains multiple modules those modules can be reordered
- Click on a module to pick it up.
- Drop the module in its desired order in the block.

### Effects on Excerpts, Search & RSS

Each modules exports a plain text version of itself to the standard WordPress `post_content` for use Searching content. The `post_content` is also used to generate excerpts for archive pages and rss feeds. Modules that do not directly contain post content, for example sidebars and widgets, should not add their content to the `post_content`.

### Default Modules

- Plain Text
	- Standard plain text input
	- Raw input good if inserting JavaScript is needed
- Rich Text
	- Includes TinyMCE Rich Text Editor
	- Does not include all the features of the WordPress rich text editor
- Widget
	- Requires the new WordPress 2.7+ Widget format
- Sidebar
	- Auto Sidebar generation
	- The only way to use Pre WordPress 2.7 Widgets
- Pullquote
	- Designed and included to show the possibilities with module output

### Advanced Module Options

Modules support the addition of custom attributes. Custom module options are responsible for saving and using the saved data. If advanced module options are available there will be a cog icon on the right side of the header when editing a module.

---


## Templates

Carrington build has support for saving layouts as templates. These layouts contain the row data needed to reproduce the layout. Templates to not contain any module data.

**To save a template:**

- Once a post has been saved with a layout the layout can then be saved as a template. 
- Click on the Actions menu, the cog icon to the right of the Tabs, and select "Save Layout as Template".
- A dialog box will appear asking for a template name and description. Enter these values and click "Save" to commit the changes.

**To use a template:**

- Templates can be selected when starting a post. 
- When starting a Carrington Build layout click on "Choose a template".
- The template chooser will appear and display all available templates.
- Upon selecting a template the template's rows will be saved in to the current post.

---
