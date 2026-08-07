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
        foreach ([12, 11, 10, 9, 8, 7, 6, 5] as $level) {
            self::assertMatchesRegularExpression(
                sprintf('/`l%dpic`\s+VARCHAR\(64\)/i', $level),
                $gameSchema,
            );
            self::assertMatchesRegularExpression(
                sprintf('/`l%dimgsvr`\s+INT\(1\)\s+NULL\s+DEFAULT\s+NULL/i', $level),
                $gameSchema,
            );
        }

        $longestRemotePath = 'users/core/' . str_repeat('a', 32) . '.jpeg?=20260807';
        self::assertGreaterThan(40, strlen($longestRemotePath));
        self::assertLessThanOrEqual(64, strlen($longestRemotePath));
    }

    public function testExistingGameMigrationWidensAllConstrainedPictureColumns(): void
    {
        $migration = file_get_contents(__DIR__ . '/../scripts/migrate-general-picture.php');
        self::assertIsString($migration);
        self::assertStringContainsString(
            'ALTER TABLE general MODIFY picture VARCHAR(64) NOT NULL',
            $migration,
        );
        self::assertStringContainsString('"l{$level}pic"', $migration);
        self::assertStringContainsString('"l{$level}imgsvr"', $migration);
        self::assertStringContainsString("ALTER TABLE emperior", $migration);
        self::assertStringContainsString("ADD COLUMN `\$imgsvrField` INT(1) NULL DEFAULT NULL", $migration);
        self::assertStringContainsString('HAVING COUNT(*) = 1', $migration);
        self::assertStringContainsString('Unmatched or ambiguous historical values remain NULL', $migration);
        self::assertStringContainsString("? 'picture_capacity'", $migration);
        self::assertStringNotContainsString('UPDATE general', $migration);
        self::assertStringContainsString("\$state === 'ready'", $migration);
    }

    public function testUnificationPreservesChiefImageServerAndCentennialMatchingUsesIt(): void
    {
        $gameRule = file_get_contents(__DIR__ . '/../hwe/func_gamerule.php');
        $candidateSql = file_get_contents(__DIR__ . '/../src/centennial_allstar_candidates.sql');
        self::assertIsString($gameRule);
        self::assertIsString($candidateSql);
        self::assertStringContainsString('name,picture,imgsvr,belong,officer_level', $gameRule);
        foreach ([12, 11, 10, 9, 8, 7, 6, 5] as $level) {
            self::assertStringContainsString(
                "'l{$level}imgsvr' => \$chiefs[{$level}]['imgsvr']",
                $gameRule,
            );
            self::assertStringContainsString("e.l{$level}imgsvr", $candidateSql);
        }
        self::assertStringContainsString('c.imgsvr IS NULL', $candidateSql);
        self::assertStringContainsString("JSON_VALUE(og.data, '$.imgsvr')", $candidateSql);
    }

    public function testScriptsDirectoryIsDeniedOverApache(): void
    {
        $accessRules = file_get_contents(__DIR__ . '/../scripts/.htaccess');
        self::assertIsString($accessRules);
        self::assertStringContainsString('Require all denied', $accessRules);
        self::assertStringContainsString('Deny from all', $accessRules);
    }
}
