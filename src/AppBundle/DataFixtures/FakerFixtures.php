<?php

// DO NOT LOAD THIS FIXTURE IT DOSENT WORK -> RUN THE FIXTURESCOMMAND INSTEAD
namespace AppBundle\DataFixtures;
 
use AppBundle\Entity\Crew;
use AppBundle\Entity\Job;
use AppBundle\Entity\Harbor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Faker;
 
class FakerFixtures extends Fixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        // ugly but i dont know how to get this fucking shit
        $doctrine = $this->container->get('doctrine');
        $em       = $doctrine->getManager();

        // maximum width and lenght of ships
        $maxWidth  = 50;
        $maxLenght = 200;

        // possible jobs for crew, harbor, ship
        $jobs      = ['trading', 'fishing', 'tourism', 'war'];

        // maximum amount of harbors, ship per harbor, crew members per ship
        $maxHarbors     = 100;
        $maxship        = 100;
        $maxCrewMembers = 200;

        // min and max accommodation capacity for harbors
        $minAccommodation = 10;
        $maxAccommodation = 1000;
        $maxDrought       = 5;

        // On configure dans quelles langues nous voulons nos données
        $faker = Faker\Factory::create('fr_FR');
 
        // insert jobs
        $jobTableName = $em->getClassMetadata(Job::class)->table["name"];
        $sql  =  'INSERT INTO '. $jobTableName .' (name) VALUES ("'. implode ('"), ("', $jobs). '");';
echo $sql;
        $stmt = $em->getConnection()->prepare($sql);
        $test = $stmt->execute();

        //recover job ids
        $query  = 'SELECT id FROM '. $jobTableName;
        $stmt   = $em->getConnection()->prepare($query);
echo $query;die();
        $jobIds = $stmt->execute()->fetchAll();
        // insert harbors
        $harbarMetas         = $em->getClassMetadata(Harbor::class);
        $harborTableName     = $harbarMetas->table["name"];
        $harborJobsTableName = $harbarMetas->associationMappings['jobs']['joinTable']['name'];

        $sql =  'INSERT INTO '. $harborTableName .' (name, drought_allowed, max_allowed_length, max_allowed_width, accommodation_capacity) VALUES ';

        for ($i = 0; $i < $maxHarbors; $i++) {
            $name                  = $faker->city;
            $droughtAllowed        = $faker->randomFloat(1, 0.5, $maxDrought);
            $maxAllowedLength      = $faker->numberBetween(1, $maxLenght);
            $maxAllowedWidth       = $faker->numberBetween(($maxAllowedLength/2), $maxWidth);
            $accommodationCapacity = $faker->numberBetween($minAccommodation, $maxAccommodation);

            $sql .= '("'. $name .'", '. $droughtAllowed .', '. $maxAllowedLength .', '. $maxAllowedWidth .', '. $accommodationCapacity .'), ';
        }

        $sql  = substr($sql, 0, -2).';';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        // insert harbor jobs
        $query     = 'SELECT id FROM '. $harborTableName;
        $stmt      = $em->getConnection()->prepare($query);
        $harborIds = $stmt->execute()->fetchAll();
        $countJobs = count($jobs);

        $sql       = 'INSERT INTO '. $harborJobsTableName .' (harbor_id, job_id) VALUES ';

        foreach ($harborIds as $harborID['id']) {
            $randomJobs  = $faker->numberBetween(1, $countJobs);
            $randomJobs  = array_rand($jobIds, $countJobs);

            foreach ($randomJobs as $randomJob) {
                $sql .= '('. $harborID .', '. $randomJob .'), ';
            }
        }

        $sql  = substr($sql, 0, -2).';';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        // insert ships


        // on créé 10 personnes
        /*for ($i = 0; $i < 10; $i++) {
            $personne = new Crew();
            $personne->setName($faker->name);
            $personne->setEmail($faker->email);
            $personne->setPassword($faker->word);
            $personne->setDateCreated($faker->dateTime($max = 'now', $timezone = null));
            $manager->persist($personne);
        }*/
 
        //$manager->flush();
    }
}