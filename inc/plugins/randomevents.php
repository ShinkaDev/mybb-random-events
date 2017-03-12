<?php
/**
 * Dice Roller MyCode
 * Copyright 2017 Shinka, All Rights Reserved
 *
 * License: http://www.mybb.com/about/license
 *
 */

// Disallow direct access to this file for security reasons
if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.');
}

$plugins->add_hook('datahandler_post_insert_post_end', 'randomevents_run');

function randomevents_info() {
	return array(
		'name'			=> 'Random Events',
		'description'	=> 'Randomly reply to active threads with customized messages!',
		'website'		=> '',
		'author'		=> 'Shinka',
		'authorsite'	=> 'https://github.com/kalynrobinson/randomevents',
		'version'		=> '0.0.1',
		'guid' 			=> '',
		'codename'		=> 'randomevents',
		'compatibility' => '18'
	);
}

function randomevents_install() {
    global $db, $mybb;

    $setting_group = array(
        'name' => 'randomevents',
        'title' => 'Random Events Settings',
        'description' => 'Randomly reply to topics with customized messages!',
        'disporder' => 5,
        'isdefault' => 0
    );

    $gid = $db->insert_query('settinggroups', $setting_group);

    $setting_array = array(
        // A select box
        'randomevents_uid' => array(
            'title' => 'User Account',
            'description' => 'ID of account used to post random events.',
            'optionscode' => 'numeric',
            'value' => 1,
            'disporder' => 1
        ),
        'randomevents_username' => array(
            'title' => 'Account Username',
            'description' => 'Username of the account used to post random events.',
            'optionscode' => 'text',
            'value' => 'Admin',
            'disporder' => 2
        ),
        'randomevents_frequency' => array(
            'title' => 'Frequency',
            'description' => 'The probability that a random event will be posted
                per reply posted in the designated forums by the designated users,
                e.g. "10" for a 10% chance.',
            'optionscode' => 'numeric',
            'value' => 5,
            'disporder' => 3
        ),
        'randomevents_forums' => array(
            'title' => 'Forums',
            'description' => 'Forums that are eligible for random events.',
            'optionscode' => 'forumselect',
            'disporder' => 4
        ),
        'randomevents_groups' => array(
            'title' => 'User Groups',
            'description' => 'User groups that are eligible for random events.',
            'optionscode' => 'groupselect',
            'disporder' => 5
        ),
        'randomevents_events' => array(
            'title' => 'Events',
            'description' => 'Messages to randomly select from when posting a
                random event. Separate messages with line breaks, e.g.
                <br />This is the first random event!
                <br />This is the second random event!',
            'optionscode' => 'textarea',
            'value' => 'This is the first random event!\nThis is the second random event!',
            'disporder' => 6
        )
    );

    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

    rebuild_settings();
}

function randomevents_is_installed() {
    global $settings;

    return isset($settings['randomevents_uid']);
}

function randomevents_uninstall() {
    global $db;

    $db->delete_query('settings', "name IN ('randomevents_uid', 'randomevents_frequency',
        'randomevents_forums', 'randomevents_groups', 'randomevents_events')");
    $db->delete_query('settinggroups', "name = 'randomevents'");

    rebuild_settings();
}

function randomevents_activate() {
}

function randomevents_deactivate() {

}

function randomevents_run($post) {
    global $mybb, $db;

    // Do not reply to self.
    if ($post->data['subject'] == 'Random Event') return;

    // Do nothing if saving draft
    if ($post->data['savedraft']) return;

    // Roll before checking if post is in valid forum by valid group
    // to reduce number of queries performed.
    $frequency = $mybb->settings['randomevents_frequency'];
    $roll = rand(1, 100);
    if ($roll > $frequency) return;

    // Check if post in valid forum.
    $forums = $mybb->settings['randomevents_forums'];
    if ($forums != -1) {
        $forums = explode(',', $forums);
        if (!in_array($post->post_insert_data['fid'], $forums)) return;
    }

    // Check if post is by valid group.
    $groups = $mybb->settings['randomevents_groups'];
    if ($groups != -1) {
        $groups = explode(',', $groups);
        $query = $db->simple_select('users', 'usergroup', "uid={$post->post_insert_data['uid']}");
        $user = $db->fetch_array($query);
        if (!in_array($user['usergroup'], $groups)) return;
    }

    // Choose random event
    $events = explode("\n", $mybb->settings['randomevents_events']);
    $roll = rand(1, count($events));
    $event = $events[$roll-1];

    // Insert post
    $uid = $mybb->settings['randomevents_uid'];
    $username = $mybb->settings['randomevents_username'];

    $insert_post_data = array(
        'tid' => $post->data['tid'],
        'replyto' => $post->data['tid'] ,
        'fid' => $post->data['fid'],
        'subject' => 'Random Event',
        'icon' => -1,
        'uid' => $uid,
        'username' => $username,
        'dateline' => TIME_NOW+1,
        'message' => $event,
        'ipaddress' => -1,
        'includesig' => 0,
        'visible' => 1
    );

    $insert = $db->insert_query('posts', $insert_post_data);
}
