<?php
class OsobneUdaje
{
    // todo: funckia na filtrovanie udajov
    // todo: aby fungoval titul
    public string $email = '';
    public string $titul = '';
    public string $meno = '';
    public string $priezvisko = '';
    public string $adresa = '';

    public function __construct(private mysqli $mysqli, public int $id=0) {
        if ($id > 0) $this->select();
    }

    private function check_database() {
        return !$this->mysqli->connect_errno;
    }

    public function insert(): int {
        // todo: pridat kontrolu vyplnenych udajov
        if ($this->check_database()) {
            $stmt = $this->mysqli->prepare('INSERT INTO osobne_udaje(email, meno, priezvisko, adresa, titul) VALUES(?, ?, ?, ?, ?)');
            $stmt->bind_param('sssss', $this->email, $this->meno, $this->priezvisko, $this->adresa, $this->titul);
            $stmt->execute();
            if (!$stmt->errno) return $stmt->insert_id;
            else return ERROR_UNKNOWN;
        }
        return ERROR_DATABASE;
    }

    public function select(): int {
        if ($this->check_database()) {
            $stmt = $this->mysqli->prepare('SELECT * FROM osobne_udaje WHERE osobne_udaje.id=?');
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                $this->email = $row['email'];
                $this->titul = $row['titul'];
                $this->meno = $row['meno'];
                $this->priezvisko = $row['priezvisko'];
                $this->adresa = $row['adresa'];
                return SUCCESS;
            } else return ERROR_USER_NOT_FOUND;
        }
        return ERROR_DATABASE;
    }

    private function email_exists(): bool {
        if ($this->check_database()) {
            $stmt = $this->mysqli->prepare('SELECT id FROM osobne_udaje WHERE email=?');
            $stmt->bind_param('s', $this->email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $result->free();
                return true;
            }
            $result->free();
        }
        return false;
    }

    public function update(): int {
        if ($this->check_database()) {
            $old_udaje = new OsobneUdaje($this->mysqli, $this->id);
            if ($this->email_exists() && $old_udaje->email != $this->email) return ERROR_USER_EXISTS;
            $stmt = $this->mysqli->prepare("UPDATE osobne_udaje SET email=?,meno=?,priezvisko=?,adresa=?,titul=? WHERE osobne_udaje.id=?");
            $stmt->bind_param('sssssi', $this->email, $this->meno, $this->priezvisko, $this->adresa, $this->titul, $this->id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) return SUCCESS;
            else return ERROR_UNKNOWN;
        }
        return ERROR_DATABASE;
    }

    public function delete(): int {
        if ($this->check_database()){
            $stmt = $this->mysqli->prepare("DELETE FROM osobne_udaje WHERE id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) return SUCCESS;
            else return ERROR_UNKNOWN;
        }
        return ERROR_DATABASE;
    }
}

class Admin
{
    public int $id_udaje = 0;
    public int $id_previerka = 0;
    public OsobneUdaje|null $udaje = null;

    public function __construct(private mysqli $mysqli, public int $id=0) {
        if ($id > 0) $this->select();
    }

    private function check_database() {
        return !$this->mysqli->connect_errno;
    }

    public function select(): int {
        if ($this->check_database()) {
            $stmt = $this->mysqli->prepare("SELECT id_udaje, id_previerka FROM admin WHERE admin.id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                $this->id_previerka = $row['id_previerka'];
                $this->id_udaje = $row['id_udaje'];
                $this->udaje = new OsobneUdaje($this->mysqli, $this->id_udaje);
                return SUCCESS;
            }
            else return ERROR_USER_NOT_FOUND;
        }
        return ERROR_DATABASE;
    }

    public function insert(string $heslo): int {
        if ($this->check_database()) {
            $udaje_id = $this->udaje->insert();
            if ($udaje_id < SUCCESS) return $udaje_id;
            $stmt = $this->mysqli->prepare("INSERT INTO admin(id_udaje, heslo) VALUES(?, ?)");
            $password_hash = password_hash($heslo, PASSWORD_DEFAULT);
            $stmt->bind_param('is', $udaje_id, $password_hash);
            $stmt->execute();
            if (!$stmt->errno) return SUCCESS;
            else return ERROR_UNKNOWN;
        }
        return ERROR_DATABASE;
    }

    public function update(): int {
        if ($this->check_database()) {

        }
        return ERROR_DATABASE;
    }

    public function delete(): int {
        if ($this->check_database()) {
            $stmt = $this->mysqli->prepare("DELETE FROM admin WHERE admin.id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                return $this->udaje->delete();
            }
            else return ERROR_UNKNOWN;
        }
        return ERROR_DATABASE;
    }
}

class Poslanec
{
    public int $id_udaje = 0;
    public int $id_previerka = 0;
    public int $id_klub = 1;
    public array $specializacia = array();
    public OsobneUdaje|null $udaje = null;
    private string $heslo = '';

    public function __construct(private mysqli $mysqli, public int $id=0) {
        if ($id > 0) $this->select();
    }

    private function check_database() {
        return !$this->mysqli->connect_errno;
    }

    public function select(): int {
        if ($this->check_database()) {
            $stmt = $this->mysqli->prepare("SELECT id_udaje, id_klub, id_previerka, specializacia FROM poslanec WHERE poslanec.id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                $this->id_previerka = $row['id_previerka'];
                $this->id_udaje = $row['id_udaje'];
                $this->id_klub = $row['id_klub'];
                $this->specializacia = explode(',', $row['specializacia']);
                $this->udaje = new OsobneUdaje($this->mysqli, $this->id_udaje);
                return SUCCESS;
            }
            else return ERROR_USER_NOT_FOUND;
        }
        return ERROR_DATABASE;
    }

    public function insert(string $heslo): int {
        if ($this->check_database()) {
            $udaje_id = $this->udaje->insert();
            if ($udaje_id < 0) return $udaje_id;
            $stmt = $this->mysqli->prepare("INSERT INTO poslanec(id_udaje, id_klub, specializacia, heslo) VALUES(?, ?, ?, ?)");
            $password_hash = password_hash($heslo, PASSWORD_DEFAULT);
            $spec_str = implode(',', $this->specializacia);
            $stmt->bind_param('is', $udaje_id, $this->id_klub, $spec_str, $password_hash);
            $stmt->execute();
            if (!$stmt->errno) return SUCCESS;
            else return ERROR_UNKNOWN;
        }
        return ERROR_DATABASE;
    }

    public function update(): int {
        if ($this->check_database()) {

        }
        return ERROR_DATABASE;
    }

    public function delete(): int {
        if ($this->check_database()) {
            $stmt = $this->mysqli->prepare("DELETE FROM poslanec WHERE poslanec.id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                return $this->udaje->delete();
            }
            else return ERROR_UNKNOWN;
        }
        return ERROR_DATABASE;
    }
}