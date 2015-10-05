<?php

namespace Wikidata\Query\Maintenance;

use Asparagus\QueryBuilder;
use Asparagus\QueryExecuter;
use MediaWiki\Logger\LoggerFactory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Wikibase\Repo\WikibaseRepo;
use Wikidata\Query\QueryRunner;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class Query extends \Maintenance {

	private $queryRunner;

	private $idParser;

	private $logger;

	public function __construct() {
		parent::__construct();

		$this->addOptions();
		$this->addArgs();
	}

	private function addOptions() {
		// @todo
	}

	private function addArgs() {
		$this->addArg( "property", "Property ID" ); $this->addArg( "value", "Main snak value" );
	}

	public function execute() {
		if ( $this->extractOptions() === false ) {
			$this->maybeHelp( true );

			return;
		}

		$this->initServices();

		$propertyId = $this->idParser->parse( $this->getArg( 0 ) );
		$valueId = $this->idParser->parse( $this->getArg( 1 ) );

		$result = $this->queryRunner->getPropertyEntityIdValueMatches( $propertyId, $valueId );

		var_export( $result );

		$this->logger->info( "$propertyId - $valueId" );
		$this->logger->info( 'Done' );
	}

	private function initServices() {
		$this->queryRunner = new QueryRunner( $this->getConfig() );
		$this->idParser = WikibaseRepo::getDefaultInstance()->getEntityIdParser();
		$this->logger = $this->newLogger();
	}

	private function newLogger() {
		$logger = new Logger( 'wikidata-query' );
		$logger->pushHandler(
			new StreamHandler( 'php://stdout' )
		);

		return $logger;
	}

	private function extractOptions() {
		return true;
	}

}

$maintClass = "Wikidata\Query\Maintenance\Query";
require_once RUN_MAINTENANCE_IF_MAIN;
