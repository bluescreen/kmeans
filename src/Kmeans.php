<?php
namespace App;

use SciPhp\NumPhp as np;
use SciPhp\NdArray;

/**
 * Class Kmeans
 * @package SciPhp
 * @see https://github.com/lazyprogrammer/machine_learning_examples/blob/master/unsupervised_class/kmeans.py
 */
class Kmeans
{
    private $costs = [];

    public function run(NdArray $X, $K, $maxIter = 20, $beta = 1.0)
    {
        list($N, $D) = $X->shape;
        $exponents = np::zeros($N, $K); // Exponents

        // initialize M with random values of X
        $M = $this->randomize($X, $K, $N, $D);

        for ($i = 0; $i < $maxIter; $i++) {

            // step 1: determine assignments / resposibilities
            for ($k = 1; $k <= $K; $k++) {
                for ($n = 1; $n <= $N; $n++) {
                    // Python:  exponents[n,k] = np.exp(-beta*d(M[k], X[n]))
                    $exponents["$n,$k"] = np::exp(-$beta * $this->distance($M[$k], $X[$n]));
                }
            }
            // Python: R = exponents / exponents.sum(axis=1, keepdims=True)
            $R = $exponents->divide($exponents->sumAxis(1, true));

            // step 2: recalculate means
            // Python: M = R.T.dot(X) / R.sum(axis=0, keepdims=True).T
            $M = $R->T->dot($X)->divide($R->sumAxis(0, true)->T);


            $cost = $this->cost($X, $R, $M);
            $this->costs[] = $cost;
            //dm("Cost", $cost, "Means", $M);
        }
        return $M;
    }

    private function randomize(NdArray $X, $K, $N, $D)
    {
        $M = np::zeros($K, $D);
        for ($k = 1; $k <= $K; $k++) {
            $random = $X[np::random()->choice($N)];
            $M["$k,1"] = $random["1"];
            $M["$k,2"] = $random["2"];
        }
        return $M;
    }

    private function cost(NdArray $X, NdArray $R, NdArray $M)
    {
        $cost = 0;
        for ($k = 1; $k <= $M->count(); $k++) {
            $diff = $X->subtract($M[$k]);
            $sq_distances = $diff->multiply($diff)->sumAxis(1);
            $cost += ($R[":,$k"]->multiply($sq_distances))->sum();
        }
        return $cost;
    }

    private function distance(NdArray $u, NdArray $v)
    {
        $diff = $u->subtract($v);
        return $diff->dot($diff);
    }

    public function costs(){
        return $this->costs;
    }
}

