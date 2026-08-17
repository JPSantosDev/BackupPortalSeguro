<?php
/**
 * Voz School - Conexão com o banco de dados
 * Ajuste as constantes abaixo para os dados do seu servidor.
 */

const DB_HOST = 'localhost';
const DB_NAME = 'voz_school';
const DB_USER = 'root';
const DB_PASS = '210509@Jean';
const DB_CHARSET = 'utf8mb4';

function garantirTabelaDenunciaTipos(PDO $pdo): void
{
    try {
        $pdo->query('SELECT 1 FROM denuncia_tipos LIMIT 1');
    } catch (Throwable $e) {
        $pdo->exec('CREATE TABLE denuncia_tipos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            denuncia_id INT NOT NULL,
            tipo_denuncia_id INT NOT NULL,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_denuncia_tipo (denuncia_id, tipo_denuncia_id),
            KEY idx_denuncia_tipo (tipo_denuncia_id),
            CONSTRAINT fk_denuncia_tipos_denuncia FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE,
            CONSTRAINT fk_denuncia_tipos_tipo FOREIGN KEY (tipo_denuncia_id) REFERENCES tipos_denuncia(id) ON DELETE CASCADE
        ) ENGINE=InnoDB');
    }
}

function migrarTiposDenunciaLegados(PDO $pdo): void
{
    $pdo->exec('INSERT IGNORE INTO denuncia_tipos (denuncia_id, tipo_denuncia_id)
        SELECT id, tipo_denuncia_id
        FROM denuncias
        WHERE tipo_denuncia_id IS NOT NULL');
}

function sqlNomeTiposDenuncia(string $alias = 'tipo_nome'): string
{
    return "(SELECT GROUP_CONCAT(DISTINCT t.nome ORDER BY t.nome SEPARATOR ', ')
            FROM denuncia_tipos dt
            JOIN tipos_denuncia t ON t.id = dt.tipo_denuncia_id
            WHERE dt.denuncia_id = d.id)
            AS {$alias}";
}

function salvarTiposDenuncia(PDO $pdo, int $denunciaId, array $tipoIds): void
{
    $tipos = array_values(array_unique(array_filter(array_map('intval', $tipoIds))));
    if ($tipos === []) {
        return;
    }

    $pdo->prepare('DELETE FROM denuncia_tipos WHERE denuncia_id = ?')->execute([$denunciaId]);

    $placeholders = implode(', ', array_fill(0, count($tipos), '(?, ?)'));
    $params = [];
    foreach ($tipos as $tipoId) {
        $params[] = $denunciaId;
        $params[] = $tipoId;
    }

    $stmt = $pdo->prepare('INSERT INTO denuncia_tipos (denuncia_id, tipo_denuncia_id) VALUES ' . $placeholders);
    $stmt->execute($params);
}

function getConexao(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        garantirTabelaDenunciaTipos($pdo);
        migrarTiposDenunciaLegados($pdo);
    }
    return $pdo;
}
