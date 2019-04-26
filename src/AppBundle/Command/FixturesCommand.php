<?php
// AppBundle/Command/fixturesCommand.php
// php -d memory_limit=256M bin/console app:fixtures
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Faker;
use AppBundle\Entity\Crew;
use AppBundle\Entity\Job;
use AppBundle\Entity\Harbor;
use AppBundle\Entity\Ship;
use AppBundle\Entity\Travel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class FixturesCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:fixtures';

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Load fixtures. i cant insert shit into the proper fixture thing is i m doing a command instead')
        ->setName('app:fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $em       = $doctrine->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // maximum width and lenght of ships
        $maxWidth  = 50;
        $maxLenght = 200;

        // possible jobs for crew, harbor, ship
        $jobs      = ['trading', 'fishing', 'tourism', 'war'];

        // maximum amount of harbors, ship per harbor, crew members per ship
        $maxHarbors     = 100;
        $maxship        = 400;
        $maxCrewMembers = 20;

        // min and max accommodation capacity for harbors
        $minAccommodation = 10;
        $maxAccommodation = 1000;
        $maxDrought       = 5;

        // On configure dans quelles langues nous voulons nos données
        $faker = Faker\Factory::create('fr_FR');
 
        // insert jobs
        $jobTableName = $em->getClassMetadata(Job::class)->table["name"];
        $sql  =  'INSERT INTO '. $jobTableName .' (name) VALUES ("'. implode ('"), ("', $jobs). '");';

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        //recover job ids
        $query  = 'SELECT id FROM '. $jobTableName;
        $stmt   = $em->getConnection()->prepare($query);

        $stmt->execute();
        $jobIds = $stmt->fetchAll();

        // insert harbors
        $harbarMetas         = $em->getClassMetadata(Harbor::class);
        $harborTableName     = $harbarMetas->table["name"];
        $harborJobsTableName = $harbarMetas->associationMappings['jobs']['joinTable']['name'];

        $sql =  'INSERT INTO '. $harborTableName .' (name, drought_allowed, max_allowed_length, max_allowed_width, accommodation_capacity) VALUES ';

        for ($i = 0; $i < $maxHarbors; $i++) {
            $name                  = $faker->city;
            $droughtAllowed        = $faker->randomFloat(1, 0.5, $maxDrought);
            $maxAllowedLength      = $faker->numberBetween(1, $maxLenght);
            $maxAllowedWidth       = ($maxWidth > ($maxAllowedLength / 2)) ? ($maxAllowedLength / 2) : $maxWidth;
            $maxAllowedWidth       = $faker->numberBetween(1, $maxAllowedWidth);
            $accommodationCapacity = $faker->numberBetween($minAccommodation, $maxAccommodation);

            $sql .= '("'. $name .'", '. $droughtAllowed .', '. $maxAllowedLength .', '. $maxAllowedWidth .', '. $accommodationCapacity .'), ';
        }

        $sql  = substr($sql, 0, -2).';';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        // insert harbor jobs
        $query     = 'SELECT * FROM '. $harborTableName;
        $stmt      = $em->getConnection()->prepare($query);
        $stmt->execute();
        $harbors   = $stmt->fetchAll();
        $countJobs = count($jobs);

        $sql       = 'INSERT INTO '. $harborJobsTableName .' (harbor_id, job_id) VALUES ';

        foreach ($harbors as $harbor) {
            $randomJobs  = $faker->numberBetween(1, $countJobs);
            $randomJobs  = array_rand($jobIds, $randomJobs);
            $randomJobs  = is_array($randomJobs) ? $randomJobs : array($randomJobs);

            foreach ($randomJobs as $randomJob) {
                $sql .= '('. $harbor['id'] .', '. $jobIds[$randomJob]['id'] .'), ';
            }
        }

        unset($randomJobs);

        $sql  = substr($sql, 0, -2).';';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        // insert ships
        $shipTableName = $em->getClassMetadata(Ship::class)->table["name"];
        $baseSql = 'INSERT INTO '. $shipTableName .' (name, unique_id, drought, length, width, capacity, power_type, engine_power, sail_max_heigh, sail_count, date_creation, harbor_built_id, job_id) VALUES ';
        $sql     = $baseSql;
        $shipCount = 0;

        foreach ($harbors as $harbor) {
            for ($i = 0; $i < $harbor['accommodation_capacity']; $i++) {
                $shipName  = $faker->catchPhrase;
                $uuid      = Uuid::uuid4()->toString();
                $drought   = $faker->randomFloat(1, 0.5, $harbor['drought_allowed']);
                $lenght    = $faker->randomFloat(1, 1, $harbor['max_allowed_length']);
                $width     = ($harbor['max_allowed_width'] > ($lenght / 2)) ? ($lenght / 2) : $harbor['max_allowed_width'];
                $width     = $faker->randomFloat(1, 1, $width);
                $capacity  = $faker->numberBetween(1, $maxCrewMembers);
                $powerType = $faker->numberBetween(0, 2);

                switch ($powerType) {
                    default :
                        $sailMaxHeigh = $faker->randomFloat(1, 1, $lenght);
                        $sailCount    = $faker->numberBetween(1, 6);
                        $enginePower  = $faker->numberBetween(50, 5000);
                    break;

                    case Ship::POWER_TYPE_SAIL :
                        $sailMaxHeigh = $faker->randomFloat(1, 1, $lenght);
                        $sailCount    = $faker->numberBetween(1, 6);
                        $enginePower  = 'NULL';
                    break;

                    case Ship::POWER_TYPE_ENGINE :
                        $sailMaxHeigh = 'NULL';
                        $sailCount    = 'NULL';
                        $enginePower  = $faker->numberBetween(50, 5000);
                    break;
                }

                $dateCreation = $faker->date($format = 'Y-m-d H:i:s', $max = 'now');
                $harborID     = $harbor['id'];

                $jobID = $faker->numberBetween(0, ($countJobs-1));
                $jobID = $jobIds[$jobID]['id'];

                $sql .= '("'. $shipName .'", "'. $uuid .'", '. $drought .', '. $lenght .', '. $width .', '. $capacity .', '. $powerType .', '. $enginePower .', '. $sailMaxHeigh .', '. $sailCount .', "'. $dateCreation .'", '. $harborID .', '. $jobID .'), ';

                $shipCount++;

                if ($shipCount % 5000 == 0) {
                    $sql  = substr($sql, 0, -2).';';
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $sql  = $baseSql;
                }
            }
        }

        unset($shipName, $uuid, $drought, $lenght, $width, $capacity, $powerType, $enginePower, $sailMaxHeigh, $sailCount, $dateCreation, $harborID, $jobID, $shipCount, $jobIds, $harbors);

        if ($sql != $baseSql) {
            $sql  = substr($sql, 0, -2).';';
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
        }

        //insert crew
        $crewTableName = $em->getClassMetadata(Crew::class)->table["name"];

        $sql   = 'SELECT id, capacity, job_id, drought, length, width, harbor_built_id, date_creation FROM '.$shipTableName;
        $stmt  = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $ships = $stmt->fetchAll();
        $crewCount = 0;

        $baseSql = 'INSERT INTO '. $crewTableName .' (firstname, lastname, birth_date, job_id, ship_id) VALUES ';
        $sql     = $baseSql;

        foreach($ships as $ship) {
            $randomCrewNumber = $faker->numberBetween(1, ceil($ship['capacity']/2));

            for ($i = 0; $i < $randomCrewNumber; $i++) {
                $sex       = ($faker->numberBetween(1, 2) == 1) ? 'male' : 'female';
                $firstName = $faker->firstName($sex);
                $lastName  = $faker->lastName;
                $birthDate = $faker->dateTimeBetween('-80 years','-18 years')->format('Y-m-d H:i:s');
                $jobID     = $ship['job_id'];
                $shipID    = $ship['id'];

                $sql .= ' ("'. $firstName .'", "'. $lastName .'", "'. $birthDate .'", '. $jobID .', '. $shipID .'), ';
                $crewCount++;

                if ($crewCount % 5000 == 0) {
                    $sql  = substr($sql, 0, -2).';';
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->execute();
                    $sql  = $baseSql;
                }
            }
        }

        unset($crewCount, $sex, $firstName, $lastName, $birthDate, $jobID, $shipID, $randomCrewNumber);

        if ($sql != $baseSql) {
            $sql  = substr($sql, 0, -2).';';
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
        }

        // DELETE FROM crew;
        // DELETE FROM travel;
        // DELETE FROM ship;
        // DELETE FROM harbor_jobs;
        // DELETE FROM job;
        // DELETE FROM harbor;
        //insert travel
        $sqlCheckHarbor = 'SELECT h.id FROM '. $harborTableName. ' h JOIN '. $harborJobsTableName .' hj ON (h.id = hj.harbor_id AND hj.job_id = :job) WHERE h.drought_allowed > :drought AND h.max_allowed_length > :length AND h.max_allowed_width > :width GROUP BY h.id';
        $travelTableName = $em->getClassMetadata(Travel::class)->table["name"];

        $baseSql = 'INSERT INTO '. $travelTableName .' (ship_id, travel_date, departure_id, arival_id) VALUES ';
        $sql     = $baseSql;
        $travelCount = 0;

        foreach($ships as $ship) {
            $availableHarborsToSaleQuery = str_replace(
                [':job',          ':drought',       ':length',       ':width'],
                [$ship['job_id'], $ship['drought'], $ship['length'], $ship['width']],
                $sqlCheckHarbor
            );

            $stmt = $em->getConnection()->prepare($availableHarborsToSaleQuery);
            $stmt->execute();
            $availableHarborsToSale = $stmt->fetchAll();

            if (count($availableHarborsToSale) > 0) {
                $lastHarbor     = $ship['harbor_built_id'];

                $randomTravelcount  = $faker->numberBetween(1, 10);
                $randomTravelcount  = (count($availableHarborsToSale) < $randomTravelcount) ? count($availableHarborsToSale) : $randomTravelcount;
                $randomHarbors      = array_rand($availableHarborsToSale, $randomTravelcount);
                unset($randomTravelcount);
                $randomHarbors      = is_array($randomHarbors) ? $randomHarbors : array($randomHarbors);

                foreach($randomHarbors as $harbor) {
                    $travelDate     = $faker->dateTimeBetween('-100 days', 'now')->format('Y-m-d H:i:s');
                    $departure      = $lastHarbor;
                    $arival         = $availableHarborsToSale[$harbor]['id'];

                    $sql           .= '('. $ship['id'] .', "'. $travelDate .'", '. $departure .', '. $arival .'), ';
                    $lastHarbor     = $availableHarborsToSale[$harbor]['id'];
                    $travelCount++;

                    if ($travelCount % 5000 == 0) {
                        $sql  = substr($sql, 0, -2).';';
                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->execute();
                        $sql  = $baseSql;
                    }
                }
            }
        }

        if ($sql != $baseSql) {
            $sql  = substr($sql, 0, -2).';';
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
        }

        $output->writeln('Fuck DataFixtures!');
    }
}

/******** queries ********/
/*

counts number of ships docked in ports
SELECT COUNT(arivals.id) as count_arival, arivals.arival_id FROM (SELECT * FROM `travel` GROUP BY ship_id ORDER BY travel_date DESC) arivals GROUP BY arivals.arival_id

counts the number of travels per ship per day
SELECT COUNT(id) as count_per_ship, DAY(travel_date), ship_id FROM travel WHERE YEAR(travel_date) = "2019" AND MONTH(travel_date) = "04" GROUP BY DAY(travel_date), ship_id ORDER BY count_per_ship desc

counts the number of ships arrived per port per day
SELECT COUNT(t.id) as count, DAY(t.travel_date), MONTH(t.travel_date), YEAR(t.travel_date), h.name FROM travel t JOIN harbor h ON (t.arival_id = h.id) WHERE YEAR(t.travel_date) = "2019" AND MONTH(t.travel_date) = "04" GROUP BY YEAR(t.travel_date), MONTH(t.travel_date), DAY(t.travel_date), t.arival_id ORDER BY count desc

 */