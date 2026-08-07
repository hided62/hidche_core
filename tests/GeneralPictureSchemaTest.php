<?php

namespace sammo;

use PHPUnit\Framework\TestCase;

final class GeneralPictureSchemaTest extends TestCase
{
    public function testGameAndAccountSchemasAcceptRemoteUserIconPaths(): void
    {
        $gameSchema = file_get_contents(__DIR__ . '/../hwe/sql/schema.sql');
        $accountSchema = file_get_contents(__DIR__ . '/../f_install/sql/common_schema.sql');
        self::assertIsString($gameSchema);
        self::assertIsString($accountSchema);
        self::assertMatchesRegularExpression('/`picture`\s+VARCHAR\(64\)\s+NOT NULL/i', $gameSchema);
        self::assertMatchesRegularExpression('/`PICTURE`\s+VARCHAR\(64\)/i', $accountSchema);

        $longestRemotePath = 'users/core/' . str_repeat('a', 32) . '.jpeg?=20260807';
        self::assertGreaterThan(40, strlen($longestRemotePath));
        self::assertLessThanOrEqual(64, strlen($longestRemotePath));
    }

    public function testExistingGameMigrationWidensOnlyThePictureColumn(): void
    {
        $migration = file_get_contents(__DIR__ . '/../scripts/migrate-general-picture.php');
        self::assertIsString($migration);
        self::assertStringContainsString(
            'ALTER TABLE general MODIFY picture VARCHAR(64) NOT NULL',
            $migration,
        );
        self::assertStringNotContainsString('UPDATE general', $migration);
        self::assertStringContainsString("if (\$state === 'ready')", $migration);
    }

    public function testScriptsDirectoryIsDeniedOverApache(): void
    {
        $accessRules = file_get_contents(__DIR__ . '/../scripts/.htaccess');
        self::assertIsString($accessRules);
        self::assertStringContainsString('Require all denied', $accessRules);
        self::assertStringContainsString('Deny from all', $accessRules);
    }
}
