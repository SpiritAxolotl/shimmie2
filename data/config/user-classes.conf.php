<?php

namespace Shimmie2;

new UserClass("user", "base", [
	Permissions::CHANGE_USER_SETTING => True,
	Permissions::BIG_SEARCH => True,
	Permissions::CREATE_COMMENT => False,
	Permissions::CREATE_IMAGE => False,
	Permissions::EDIT_IMAGE_TAG => False,
	Permissions::EDIT_IMAGE_SOURCE => False,
	Permissions::EDIT_IMAGE_TITLE => False,
	Permissions::EDIT_IMAGE_RELATIONSHIPS => False,
	Permissions::EDIT_IMAGE_ARTIST => False,
	Permissions::CREATE_IMAGE_REPORT => False,
	Permissions::SEND_PM => False,
	Permissions::READ_PM => False,
	Permissions::CREATE_VOTE => False,
	Permissions::EDIT_IMAGE_RATING => False,
	Permissions::PERFORM_BULK_ACTIONS => False,
	Permissions::EDIT_FAVOURITES => True,
	Permissions::FORUM_CREATE => False,
	Permissions::NOTES_CREATE => False,
	Permissions::NOTES_EDIT => False,
	Permissions::NOTES_REQUEST => True,
	Permissions::POOLS_CREATE => False,
	Permissions::POOLS_UPDATE => False,
	Permissions::SET_PRIVATE_IMAGE => False,
	Permissions::BULK_DOWNLOAD => False
]);

new UserClass("hellbanned", "base", [
	Permissions::CREATE_COMMENT => False
]);

new UserClass("trusted", "user", [
	Permissions::BULK_DOWNLOAD => True
]);