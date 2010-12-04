<?php
/**
 * Translates a category
 * 
 * PHP Version 5.2
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * @category  phpMyFAQ
 * @package   Administration
 * @author    Thorsten Rinne <thorsten@phpmyfaq.de>
 * @author    Rudi Ferrari <bookcrossers@gmx.de>
 * @copyright 2006-2010 phpMyFAQ Team
 * @license   http://www.mozilla.org/MPL/MPL-1.1.html Mozilla Public License Version 1.1
 * @link      http://www.phpmyfaq.de
 * @since     2006-09-10
 */

if (!defined('IS_VALID_PHPMYFAQ')) {
    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));
    exit();
}

if ($permission["editcateg"]) {
    $category = new PMF_Category($current_admin_user, $current_admin_groups, false);
    $category->getMissingCategories();
    $id     = PMF_Filter::filterInput(INPUT_GET, 'cat', FILTER_VALIDATE_INT);
    $header = sprintf('%s %s: <em>%s</em>',
        $PMF_LANG['ad_categ_trans_1'],
        $PMF_LANG['ad_categ_trans_2'],
        $category->categoryName[$id]['name']);

    $selected_lang = PMF_Filter::filterInput(INPUT_POST, 'trlang', FILTER_SANITIZE_STRING, $LANGCODE);
    if ($selected_lang != $LANGCODE) {
        $action  = "showcategory";
        $showcat = "yes";
    } else {
        $action  = "updatecategory";
        $showcat = "no";
    }

    printf('<h2>%s</h2>', $header);
?>
    <form action="?action=updatecategory" method="post">
    <fieldset>
    <legend><?php print $header; ?></legend>

        <input type="hidden" name="id" value="<?php print $id; ?>" />
        <input type="hidden" name="parent_id" value="<?php print $category->categoryName[$id]["parent_id"]; ?>" />
        <input type="hidden" name="showcat" value="<?php print $showcat; ?>" />
        <input type="hidden" name="csrf" value="<?php print $user->getCsrfTokenFromSession(); ?>" />

<?php
    if ($faqconfig->get('main.enableGoogleTranslation') === true) {
?>    
        <input type="hidden" id="name" name="name" value="<?php print $category->categoryName[$id]['name']; ?>" />
        <input type="hidden" id="lang" name="lang" value="<?php print $selected_lang; ?>" />
        <input type="hidden" id="description" name="description" value="<?php print $category->categoryName[$id]['description']; ?>" />
        
        <div id="editTranslations">
            <label class="left" for="langTo"><?php print $PMF_LANG["ad_entry_locale"]; ?>:</label>
            <?php print PMF_Language::selectLanguages($faqData['lang'], false, array(), 'langTo'); ?>
            <br />
            <input type="hidden" name="used_translated_languages" id="used_translated_languages" value="" />
            <div id="getedTranslations">
            </div>
        </div>
        <br />
<?php
    } else {
?>           
        <label class="left"><?php print $PMF_LANG["ad_categ_titel"]; ?>:</label>
        <input type="text" name="name" size="30" style="width: 250px;" value="" /><br />

        <label class="left"><?php print $PMF_LANG["ad_categ_lang"]; ?>:</label>
        <select name="lang" size="1">
        <?php print $category->getCategoryLanguagesToTranslate($id, $selected_lang); ?>
        </select><br />

        <label class="left"><?php print $PMF_LANG["ad_categ_desc"]; ?>:</label>
        <input type="text" name="description" size="30" style="width: 250px;" value="" /><br />

<?php
    }
?>            
        <label class="left"><?php print $PMF_LANG["ad_categ_owner"]; ?>:</label>
        <select name="user_id" size="1">
        <?php print $user->getAllUserOptions($category->categoryName[$id]['user_id']); ?>
        </select><br />

        <input class="submit" style="margin-left: 190px;" type="submit" name="submit" value="<?php print $PMF_LANG["ad_categ_translatecateg"]; ?>" />
        <br /><hr />
<?php
           print '<strong>'.$PMF_LANG["ad_categ_transalready"].'</strong><br />';
           foreach ($category->getCategoryLanguagesTranslated($id) as $language => $namedesc) {
              print "&nbsp;&nbsp;&nbsp;<strong style=\"vertical-align: top;\">&middot; " . $language . "</strong>: " . $namedesc . "\n<br />";
           }
?>
    </fieldset>
    </form>
<?php 
    if ($faqconfig->get('main.enableGoogleTranslation') === true) {
?>        
    <script src="https://www.google.com/jsapi?key=<?php echo $faqconfig->get('main.googleTranslationKey')?>" type="text/javascript"></script>
    <script type="text/javascript">
    /* <![CDATA[ */
    google.load("language", "1");

    var langFromSelect = $("#lang");
    var langToSelect   = $("#langTo");       
    
    $("#langTo").val($("#lang").val());
        
    // Add a onChange to the translation select
    langToSelect.change(
        function() {
            var langTo = $(this).val();

            if (!document.getElementById('name_translated_' + langTo)) {

                // Add language value
                var languages = $('#used_translated_languages').val();
                if (languages == '') {
                    $('#used_translated_languages').val(langTo);
                } else {
                    $('#used_translated_languages').val(languages + ',' + langTo);
                }
               
                var fieldset = $('<fieldset></fieldset>')
                    .append($('<legend></legend>').html($("#langTo option:selected").text()));

                // Text for title
                fieldset
                    .append($('<label></label>').attr({for: 'name_translated_' + langTo}).addClass('left')
                        .append('<?php print $PMF_LANG["ad_categ_titel"]; ?>'))
                    .append($('<input></input>')
                        .attr({id:        'name_translated_' + langTo,
                               name:      'name_translated_' + langTo,
                               readonly:  'readonly',
                               maxlength: '255',
                               size:      '30',
                               style:     'width: 300px;'}))
                    .append($('<br></br>'));
                    
                // Textarea for description
                fieldset
                    .append($('<label></label>').attr({for: 'description_translated_' + langTo}).addClass('left')
                        .append('<?php print $PMF_LANG["ad_categ_desc"]; ?>'))                
                    .append($('<textarea></textarea>')
                        .attr({id:       'description_translated_' + langTo,
                               name:     'description_translated_' + langTo,
                               readonly: 'readonly',
                               cols:     '80',
                               rows:     '3',
                               style:    'width: 300px;'}))

                $('#getedTranslations').append(fieldset);
            }

            // Set the translated text
            var langFrom = $('#lang').val();
            getGoogleTranslation('#name_translated_' + langTo, $('#name').val(), langFrom, langTo);
            getGoogleTranslation('#description_translated_' + langTo, $('#description').val(), langFrom, langTo);
        }
    );
    /* ]]> */
    </script>
<?php
    }
} else {
    print $PMF_LANG["err_NotAuth"];
}
