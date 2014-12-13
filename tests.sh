#!/bin/sh

print_header () {
    echo "\n\x1B[34m****$1****\x1B[39m\n"
}

print_error () {
    echo "\x1B[31m$1\x1B[39m"
}

print_success () {
    echo "\x1B[32m$1\x1B[39m"
}

print_info () {
    echo "\x1B[33m$1\x1B[39m"
}

test_command_output () {
    COMMAND=$1
    EXPECTED=$2
    ARGS=${@:3}
    OUTPUT=`eecli $COMMAND --no-ansi $ARGS | tail -1`

    ((NUM_TESTS+=1))

    if [[ $OUTPUT == $EXPECTED ]]; then

        ((NUM_PASSED+=1))

        test_command_passed "$COMMAND$ARGS"

    else

        ((NUM_FAILED+=1))

        test_command_failed "$COMMAND$ARGS" "$OUTPUT"

    fi
}

test_command_file_exists () {
    COMMAND=$1
    EXPECTED=$2
    ARGS=${@:3}
    OUTPUT=`eecli $COMMAND --no-ansi $ARGS | tail -1`

    ((NUM_TESTS+=1))

    if [[ -e $EXPECTED ]]; then

        ((NUM_PASSED+=1))

        test_command_passed "$COMMAND$ARGS"

    else

        ((NUM_FAILED+=1))

        test_command_failed "$COMMAND$ARGS" "$OUTPUT"

    fi
}

test_command_failed () {
    if [[ -n $EECLI_VERBOSITY ]]; then
        print_error "$1"

        echo "OUTPUT WAS: $2"
    fi
}

test_command_passed () {
    if [[ -n $EECLI_VERBOSITY ]]; then
        print_success "$1"
    fi
}

test_ee_version () {
    EE_VERSION=$1
    TEST_EE_PATH=$2
    TEST_SQL_DB=$3
    TEST_SQL_PATH=$4

    NUM_TESTS=0
    NUM_PASSED=0
    NUM_FAILED=0

    print_header "Testing EE $1"

    # go to ee path
    cd $TEST_EE_PATH

    # remove old config file
    rm -f .eecli.php

    # import db
    mysql $TEST_SQL_DB < $TEST_SQL_PATH

    # delete all files we'll generate
    rm -rf .eecli.php FooCommand.php .htaccess system/expressionengine/third_party/json

    # init
    test_command_file_exists "init" .eecli.php

    # cache:clear:ee
    test_command_output "cache:clear:ee" "EE cache cleared."
    test_command_output "cache:clear:ee db" "EE db cache cleared."
    test_command_output "cache:clear:ee page" "EE page cache cleared."
    test_command_output "cache:clear:ee tag" "EE tag cache cleared."
    test_command_output "cache:clear:ee invalid" "Invalid cache type"

    # cache:clear:ce_cache
    test_command_output "cache:clear:ce_cache" "CE Cache cleared."

    # cache:clear:stash
    test_command_output "cache:clear:stash" "Stash cache cleared."

    # create:category_group
    if [[ $EE_VERSION > 2.8 ]]; then
        FAILURE_MESSAGE="The Group Name field may only contain alpha-numeric characters, underscores, dashes, and spaces."
    else
        FAILURE_MESSAGE="The name you submitted may only contain alpha-numeric characters, spaces, underscores, and dashes"
    fi

    test_command_output "create:category_group CatGroup" "Category group CatGroup (1) created."
    test_command_output "create:category_group @" "$FAILURE_MESSAGE"

    # create:category
    test_command_output "create:category Cat 1" "Category Cat (1) created."
    test_command_output "create:category @ 1" "Unable to create valid Category URL Title for your Category"

    # create:channel
    test_command_output "create:channel Foo" "Channel Foo (1) created."
    test_command_output "create:channel @" "Your channel name must contain only alpha-numeric characters and no spaces."

    # create:field_group
    test_command_output "create:field_group Foo" "Field group Foo (1) created."
    test_command_output "create:field_group @" "The name you submitted may only contain alpha-numeric characters, spaces, underscores, and dashes"

    # create:upload_pref
    test_command_output "create:upload_pref Uploads ./uploads/ /uploads/" "File upload destination Uploads created."
    test_command_output "create:upload_pref --max_size=foo @ @ @" "The max_size field must contain only numbers."

    if [[ $EE_VERSION > 2.6 ]]; then
        FAILURE_MESSAGE="The field name you submitted contains invalid characters"
    else
        FAILURE_MESSAGE="The field name you submitted contains invalid characters: @"
    fi

    # create:field:assets
    test_command_output "create:field:assets assets assets 1" "Field assets (1) created."
    test_command_output "create:field:assets @ @ 1" "$FAILURE_MESSAGE"

    # create:field:checkboxes
    test_command_output "create:field:checkboxes checkboxes checkboxes 1" "Field checkboxes (2) created."
    test_command_output "create:field:checkboxes @ @ 1" "$FAILURE_MESSAGE"

    # create:field:date
    test_command_output "create:field:date date_field date_field 1" "Field date_field (3) created."
    test_command_output "create:field:date @ @ 1" "$FAILURE_MESSAGE"

    # create:field:file
    test_command_output "create:field:file file file 1" "Field file (4) created."
    test_command_output "create:field:file @ @ 1" "$FAILURE_MESSAGE"

    # create:field:matrix
    test_command_output "create:field:matrix matrix matrix 1" "Field matrix (5) created."
    test_command_output "create:field:matrix @ @ 1" "$FAILURE_MESSAGE"

    # create:field:multi_select
    test_command_output "create:field:multi_select multi_select multi_select 1" "Field multi_select (6) created."
    test_command_output "create:field:multi_select @ @ 1" "$FAILURE_MESSAGE"

    # create:field:playa
    test_command_output "create:field:playa playa playa 1" "Field playa (7) created."
    test_command_output "create:field:playa @ @ 1" "$FAILURE_MESSAGE"

    # create:field:radio
    test_command_output "create:field:radio radio radio 1" "Field radio (8) created."
    test_command_output "create:field:radio @ @ 1" "$FAILURE_MESSAGE"

    # create:field:rte
    test_command_output "create:field:rte rte rte 1" "Field rte (9) created."
    test_command_output "create:field:rte @ @ 1" "$FAILURE_MESSAGE"

    # create:field:select
    test_command_output "create:field:select select select 1" "Field select (10) created."
    test_command_output "create:field:select @ @ 1" "$FAILURE_MESSAGE"

    # create:field:text
    test_command_output "create:field:text text text 1" "Field text (11) created."
    test_command_output "create:field:text @ @ 1" "$FAILURE_MESSAGE"

    # create:field:textarea
    test_command_output "create:field:textarea textarea textarea 1" "Field textarea (12) created."
    test_command_output "create:field:textarea @ @ 1" "$FAILURE_MESSAGE"

    # create:field:wygwam
    test_command_output "create:field:wygwam wygwam wygwam 1" "Field wygwam (13) created."
    test_command_output "create:field:wygwam @ @ 1" "$FAILURE_MESSAGE"

    if [[ $EE_VERSION > 2.5 ]]; then
        # create:field:fieldpack_checkboxes
        test_command_output "create:field:fieldpack_checkboxes fieldpack_checkboxes fieldpack_checkboxes 1" "Field fieldpack_checkboxes (14) created."
        test_command_output "create:field:fieldpack_checkboxes @ @ 1" "$FAILURE_MESSAGE"

        # create:field:fieldpack_dropdown
        test_command_output "create:field:fieldpack_dropdown fieldpack_dropdown fieldpack_dropdown 1" "Field fieldpack_dropdown (15) created."
        test_command_output "create:field:fieldpack_dropdown @ @ 1" "$FAILURE_MESSAGE"

        # create:field:fieldpack_list
        test_command_output "create:field:fieldpack_list fieldpack_list fieldpack_list 1" "Field fieldpack_list (16) created."
        test_command_output "create:field:fieldpack_list @ @ 1" "$FAILURE_MESSAGE"

        # create:field:fieldpack_multiselect
        test_command_output "create:field:fieldpack_multiselect fieldpack_multiselect fieldpack_multiselect 1" "Field fieldpack_multiselect (17) created."
        test_command_output "create:field:fieldpack_multiselect @ @ 1" "$FAILURE_MESSAGE"

        # create:field:fieldpack_pill
        test_command_output "create:field:fieldpack_pill fieldpack_pill fieldpack_pill 1" "Field fieldpack_pill (18) created."
        test_command_output "create:field:fieldpack_pill @ @ 1" "$FAILURE_MESSAGE"

        # create:field:fieldpack_radio_buttons
        test_command_output "create:field:fieldpack_radio_buttons fieldpack_radio_buttons fieldpack_radio_buttons 1" "Field fieldpack_radio_buttons (19) created."
        test_command_output "create:field:fieldpack_radio_buttons @ @ 1" "$FAILURE_MESSAGE"

        # create:field:fieldpack_switch
        test_command_output "create:field:fieldpack_switch fieldpack_switch fieldpack_switch 1" "Field fieldpack_switch (20) created."
        test_command_output "create:field:fieldpack_switch @ @ 1" "$FAILURE_MESSAGE"
    fi

    # create:global_variable
    test_command_output "create:global_variable foo" "Global variable foo created."
    test_command_output "create:global_variable @" "The name you submitted may only contain alpha-numeric characters, underscores, and dashes"

    # create:member
    test_command_output "create:member -H rob+test@robsanchez.com" "Member rob+test@robsanchez.com (2) created."
    test_command_output "create:member @" "The email you submitted is not valid"

    # create:member_group
    test_command_output "create:member_group Foo" "Member group Foo (6) created."
    test_command_output "create:member_group Members" "There is already a Member Group with that name."

    # create:snippet
    test_command_output "create:snippet foo" "Snippet foo created."
    test_command_output "create:snippet @" "The name you submitted may only contain alpha-numeric characters, underscores, and dashes"

    # create:status
    test_command_output "create:status draft 1" "Status draft (3) created."
    test_command_output "create:status open 1" "A status already exists with the same name."

    # create:status_group
    test_command_output "create:status_group Foo" "Status group Foo (2) created."
    test_command_output "create:status_group @" "The name you submitted may only contain alpha-numeric characters, spaces, underscores, and dashes"

    # create:template
    test_command_output "create:template site/foo" "Template site/foo (2) created."
    test_command_output "create:template @" "Template @ must be in <template_group>/<template_name> format."

    # create:template_group
    test_command_output "create:template_group foo" "Template group foo (2) created."
    test_command_output "create:template_group @" "The name you submitted may only contain alpha-numeric characters, underscores, and dashes"

    # db:dump
    test_command_output "db:dump --name=foo" "./foo.sql created."

    # delete:global_variable
    test_command_output "delete:global_variable --force foo" "Global variable foo deleted."
    test_command_output "delete:global_variable --force @" "Global variable @ not found."

    # delete:snippet
    test_command_output "delete:snippet --force foo" "Snippet foo deleted."
    test_command_output "delete:snippet --force @" "Snippet @ not found."

    # delete:template
    test_command_output "delete:template --force site/foo" "Template site/foo deleted."
    test_command_output "delete:template --force site/bar" "Template site/bar not found."

    # delete:template_group
    test_command_output "delete:template_group --force foo" "Template group foo deleted."
    test_command_output "delete:template_group --force bar" "Template group bar not found."

    # delete:entry
    mysql -e "INSERT INTO exp_channel_titles (entry_id, channel_id, author_id, title, url_title, \`status\`) VALUES (1, 1, 1, 'Your Entry Title', 'your_entry_title', 'open');" $TEST_SQL_DB
    mysql -e "INSERT INTO exp_channel_data (entry_id, channel_id) VALUES (1, 1);" $TEST_SQL_DB

    test_command_output "delete:entry --force 1" "Entry “Your Entry Title” (1) deleted."
    test_command_output "delete:entry --force 2" "Entry 2 not found."

    # generate:command
    test_command_file_exists "generate:command foo" FooCommand.php

    # generate:htaccess
    test_command_file_exists "generate:htaccess" .htaccess

    # install:addon
    test_command_file_exists "install:addon json" system/expressionengine/third_party/json/pi.json.php

    # update:addons
    test_command_output "update:addons modules" "Modules already up-to-date."
    test_command_output "update:addons extensions" "Extensions already up-to-date."
    test_command_output "update:addons fieldtypes" "Fieldtypes already up-to-date."
    test_command_output "update:addons accessories" "Accessories already up-to-date."

    echo

    if [[ $NUM_PASSED > 0 ]]; then
        print_info "$NUM_PASSED / $NUM_TESTS tests passed."
    fi

    if [[ $NUM_FAILED > 0 ]]; then
        print_error "$NUM_FAILED / $NUM_TESTS tests failed."
    fi
}
