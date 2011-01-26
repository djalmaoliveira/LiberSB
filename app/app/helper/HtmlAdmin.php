<?

function htmlAdmin_tab_($tabs, $extras='') {

    $tabs_name = '';
    foreach($tabs as $tab_name => $url) {
        $tabs_name .=  "<div class='tabs' id='tab_$tab_name' onclick='openTab(\"$url\")'>$tab_name</div>\n";
    }

    $html_tab = "
        <div id='tab_area'>
            $tabs_name
            <div id='tab_content'></div>
        </div>
        <script type=\"text/javascript\">
            function openTab(url) {
                $('#tab_content').load(url);
            }
        </script>
    ";
    echo $html_tab;
}

?>
