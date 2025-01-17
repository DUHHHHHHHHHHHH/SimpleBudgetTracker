<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../config.php";

$db = new Database();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoria_id = isset($_POST["CATEGORIA_ID"]) ? $_POST["CATEGORIA_ID"] : null;
    $utente_id = isset($_POST["UTENTE_ID"]) ? $_POST["UTENTE_ID"] : null;

    if (!empty($categoria_id) && !empty($utente_id)) {
        try {
            $conn = mysqli_connect($db->host, $db->user, $db->password, $db->db_name);

            if (!$conn) {
                throw new Exception("Connessione al database fallita: " . mysqli_connect_error());
            }

            $query = "SELECT 
                        c.CATEGORIA_ID, 
                        c.CATEGORIA_Nome, 
                        c.CATEGORIA_Descrizione, 
                        c.CATEGORIA_Budget, 
                        c.TIPOLOGIA_FK_ID, 
                        c.UTENTE_FK_ID, 
                        t.TIPOLOGIA_Nome 
                      FROM 
                        categoria c
                      JOIN 
                        tipologia t 
                      ON 
                        c.TIPOLOGIA_FK_ID = t.TIPOLOGIA_ID
                      WHERE 
                        c.UTENTE_FK_ID = ? AND c.CATEGORIA_ID = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'is', $utente_id, $categoria_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                mysqli_stmt_bind_result($stmt, $CATEGORIA_ID, $CATEGORIA_ID, $CATEGORIA_Descrizione, $CATEGORIA_Budget, $TIPOLOGIA_FK_ID, $UTENTE_FK_ID, $TIPOLOGIA_Nome);
                mysqli_stmt_fetch($stmt);

                $categoria = array(
                    "CATEGORIA_ID" => $CATEGORIA_ID,
                    "CATEGORIA_Nome" => $CATEGORIA_Nome,
                    "CATEGORIA_Descrizione" => $CATEGORIA_Descrizione,
                    "CATEGORIA_Budget" => $CATEGORIA_Budget,
                    "TIPOLOGIA_FK_ID" => $TIPOLOGIA_FK_ID,
                    "UTENTE_FK_ID" => $UTENTE_FK_ID,
                    "TIPOLOGIA_Nome" => $TIPOLOGIA_Nome
                );

                echo json_encode($categoria);
            } else {
                echo json_encode(array("message" => "Categoria non trovata per l'utente specificato."));
            }

            mysqli_stmt_close($stmt);
            mysqli_close($conn);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(array("message" => $e->getMessage()));
        }
    } else {
        echo json_encode(array("message" => "Tutti i campi sono richiesti.", "code" => 400));
    }
} else {
    echo json_encode(array("message" => "Metodo non consentito.", "code" => 405));
}
?>
