<?php

use App\Kmeans;
use PHPUnit\Framework\TestCase;
use SciPhp\NumPhp as np;

class KMeansTest extends TestCase
{
    public function testRunKmeans()
    {
        $D = 2; // Dimension
        $s = 6;  // Distanz
        $N = 30; // Anzahl Punkte
        $mu1 = np::ar([0, 0]);
        $mu2 = np::ar([$s, $s]);
        $mu3 = np::ar([0, $s]);

        $X1 = np::random()->randn($N, $D)->add($mu1);
        $X2 = np::random()->randn($N, $D)->add($mu2);
        $X3 = np::random()->randn($N, $D)->add($mu3);

        $X = $X1->concat($X2)->concat($X3);

        $kmeans = new Kmeans();
        $result = $kmeans->run($X, 3, 20, 1.0);

        dd($kmeans->costs());
    }
}
