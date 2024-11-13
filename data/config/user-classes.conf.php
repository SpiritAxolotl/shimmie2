<?php

namespace Shimmie2;

new UserClass("hellbanned", "base", [
	Permissions::CREATE_COMMENT => False
]);

new UserClass("trusted", "user");