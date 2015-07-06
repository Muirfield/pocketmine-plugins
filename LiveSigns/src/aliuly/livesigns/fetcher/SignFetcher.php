<?php
namespace aliuly\livesigns\fetcher;

abstract class SignFetcher {
	static public function fetch($dat,$cfg) {
		return ["","","",""];
	}
	static public function default_age() { return -1; }
}
