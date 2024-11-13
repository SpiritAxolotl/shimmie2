<?php

namespace Shimmie2;

new UserClass("anonymous", "base", [
	Permissions::CREATE_COMMENT => False
]);

new UserClass("hellbanned", "base", [
	Permissions::CREATE_COMMENT => False
]);

new UserClass("trusted", "user");