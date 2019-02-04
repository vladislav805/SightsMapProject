<?

	namespace Model;

	class Moderator extends ExtendedUser {

		public function jsonSerialize() {
			return [
				"userId" => $this->getId(),
				"firstName" => $this->getFirstName(),
				"lastName" => $this->getLastName(),
				"login" => $this->getLogin(),
				"status" => $this->getStatus()
			];
		}

	}