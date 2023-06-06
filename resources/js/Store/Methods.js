import { ref } from "vue";

//check condition if array and return boolean if based on condition
/* 
    * val = String
    * condition = logical condition
    * number = number to compare
*/
const ifArray = (val, condition, number) => {
    if(Array.isArray(val)){
        if(condition == '>'){
            return greater_than(val.length, parseInt(number))
        }else if (condition == '<') {
            return less_than(val.length, parseInt(number))
        }else if (condition == '==') {
            return equal(val.length, parseInt(number))
        }else if (condition == '<=') {
            return less_than_equal(val.length, parseInt(number))
        }else if (condition == '>=') {
            return greater_than_equal(val.length, parseInt(number))
        }
        else
            return false
    }
    else
        return false
}

const greater_than = (val1, val2) => {
    return parseFloat(val1) > parseFloat(val2)
}
const less_than = (val1, val2) => {
    return parseFloat(val1) < parseFloat(val2)
}
const equal = (val1, val2) => {
    return parseFloat(val1) == parseFloat(val2)
}
const greater_than_equal = (val1, val2) => {
    return parseFloat(val1) >= parseFloat(val2)
}
const less_than_equal = (val1, val2) => {
    return parseFloat(val1) <= parseFloat(val2)
}

const resizedElement = (element, cb) => {
    let resize = new ResizeObserver(() => {
        cb()
    });

    resize.observe(element)
}

const documentContextMenus = (element) => {
    window.addEventListener('contextmenu', e => {
        if(e.target.closest('.documents'))
        {
            element.classList.remove('hidden')

            let x = e.clientX, y = e.clientY, winWidth = window.innerWidth,
            winHeight = window.innerHeight, cmWidth = element.offsetWidth,
            cmHeight = element.offsetHeight

            x = x > winWidth - cmWidth ? (winWidth - (cmWidth + 20))  : x
            y = y > winHeight - cmHeight ? winHeight - (cmHeight + 10) : y

            element.style.top = y + 'px'
            element.style.left = x + 'px'

        }else{
            element.classList.add('hidden')
        }
    })
}

const MoreMenus = (documentEl, element) => {
    window.addEventListener('click', e => {
        if(e.target.closest('.moreMenuVersion'))
        {
            element.classList.remove('hidden')

            let x = e.clientX, y = e.clientY, winWidth = window.innerWidth,
            winHeight = window.innerHeight, cmWidth = element.offsetWidth,
            cmHeight = element.offsetHeight

            x = x > winWidth - cmWidth ? (winWidth + (cmWidth + 10))  : x
            y = y > winHeight - cmHeight ? winHeight - (cmHeight + 10) : y

            element.style.top = (y-20) + 'px'
            element.style.left = (x-170) + 'px'

        }else{
            e.target.closest('#versionOption') ? '' :
                element.classList.add('hidden')
        }
    })
}

const authorized = (auth, tab) => {
    if([1, 6].includes(auth)) //admin
    {
        return true
    }else{
        if(auth == 2 && [1, 6, 7].includes(tab)) //user
        {
            return true
        }else if(auth == 3 && [1, 2, 3, 6, 7].includes(tab))
        {
            return true
        }else if(auth == 4 && [1, 3, 6, 7].includes(tab))
        {
            return true
        }
    }
}

const findElement = (arr, index) => {
    return arr.find((val, indx) => {
        return indx === index
    })
}

const role = role => {
    return role ? 'Task Force Chairperson' : null
}

const getTaskForce = (area, accredlvl) => {
    return axios.get(`/task/task_force_members/${area}/${accredlvl}`)
}

const K_means = (mean, dataset, maxIteration = 100) => {
    
    const iterations = ref(0)

    function meanCluster(clusters) {
        let initialized = {};

        for (let mean in clusters) {
            initialized[clusters[mean]] = [];
        }

        //console.log(initialized)
        return initialized;
    }

    function assignClusters(clusters, seeds, assignments) {
        let cluster = null

        seeds.forEach((seed, index) => {
            cluster = distance(clusters, seed);
            assignments[cluster].push(seed);
        });

        return assignments;
    }

    function distance (clusters, value) {
        let scores = {};
        let score_values = [];

        clusters.forEach(cluster => {
            //get difference
            let calc = Math.abs(value - cluster)

            scores[cluster] = calc
        });

        for (let key in scores)
        {
            score_values.push(scores[key]);
        }
        let min_val = score_values.reduce(function (a,b) { return ( a < b ? a : b); })

        // return the cluster with the minimum value
        for (var key in scores) {
            if (scores[key] == min_val) {
                var cluster = key;
                break;
            }
        }

        return cluster;
    }

    function clacMean(a, n) {
        if (typeof n != 'number') {
            n = a.length;
        }

        return a.reduce(function (c, d) { return c + d }) / n;
    }

    function createNewCentroids(assignments) {
        let centroids = []
        for (let key in assignments)
        {
            assignments[key].length > 0 ? 
                centroids.push(Number(clacMean(assignments[key]).toFixed(2))) :
                centroids.push(key)
        }

        return centroids;
    }

    function compareClus(a, b) {
        for (let i = 0; i < a.length; i++) {
            let matched = false;
            for (let j = 0; j < b.length; j++) {
                if (b[j] === a[i]) {
                    matched = true;
                }
            }

            if (matched == false) {
                return true;
            }
        }

        return false;
    }

    function iterate(clusters, seeds, k){
        iterations.value++

        let assignments = meanCluster(clusters)

            assignments = assignClusters(clusters, seeds, assignments)

        let centroids = createNewCentroids(assignments)

        let changed = compareClus(clusters, centroids)

        if (changed == true && iterations.value < k) {
            return iterate(centroids, seeds, k);
        }else{
            return {"iterations": iterations.value, "result": assignments};
        }
        //return clusters
    }
    iterations.value

    return iterate(mean, dataset, maxIteration)
}

const isValidJson = (str) => {
    try {
        return JSON.parse(str);
    } catch (e) {
        return false;
    }
}

const isValidJsonAndEmpty = (str) => {
    try {
        let data = isValidJson(str)
        if(data) {
            let filtered = data.filter(obj => {
                return obj.evidence !== null;
            })
            return filtered.length == 0? false : filtered;
        }
    } catch (e) {
        return false;
    }
}

    /*   function numericSort(array) {
    return (
      array
        // ensure the array is not changed in-place
        .slice()
        // comparator function that treats input as numeric
        .sort(function (a, b) {
          return a - b;
        })
    );
  }
  
  function uniqueCountSorted(input) {
    var uniqueValueCount = 0;
    var lastSeenValue;
    for (var i = 0; i < input.length; i++) {
      if (i === 0 || input[i] !== lastSeenValue) {
        lastSeenValue = input[i];
        uniqueValueCount++;
      }
    }
    return uniqueValueCount;
  }
  
  function makeMatrix(columns, rows) {
    var matrix = [];
    for (var i = 0; i < columns; i++) {
      var column = [];
      for (var j = 0; j < rows; j++) {
        column.push(0);
      }
      matrix.push(column);
    }
    return matrix;
  }
  
  function ssq(j, i, sumX, sumXsq) {
    var sji; // s(j, i)
    if (j > 0) {
      var muji = (sumX[i] - sumX[j - 1]) / (i - j + 1); // mu(j, i)
      sji = sumXsq[i] - sumXsq[j - 1] - (i - j + 1) * muji * muji;
    } else {
      sji = sumXsq[i] - (sumX[i] * sumX[i]) / (i + 1);
    }
    return sji < 0 ? 0 : sji;
  }
  
  function fillMatrixColumn(imin, imax, column, matrix, backtrackMatrix, sumX, sumXsq) {
    if (imin > imax) {
      return;
    }
  
    // Start at midpoint between imin and imax
    var i = Math.floor((imin + imax) / 2);
  
    // Initialization of S[k][i]:
    matrix[column][i] = matrix[column - 1][i - 1];
    backtrackMatrix[column][i] = i;
  
    var jlow = column; // the lower end for j
  
    if (imin > column) {
      jlow = Math.max(jlow, backtrackMatrix[column][imin - 1] || 0);
    }
    jlow = Math.max(jlow, backtrackMatrix[column - 1][i] || 0);
  
    var jhigh = i - 1; // the upper end for j
    if (imax < matrix[0].length - 1) {
      jhigh = Math.min(jhigh, backtrackMatrix[column][imax + 1] || 0);
    }
  
    var sji;
    var sjlowi;
    var ssqjlow;
    var ssqj;
    for (var j = jhigh; j >= jlow; --j) {
      // compute s(j,i)
      sji = ssq(j, i, sumX, sumXsq);
  
      // MS May 11, 2016 Added:
      if (sji + matrix[column - 1][jlow - 1] >= matrix[column][i]) {
        break;
      }
  
      // Examine the lower bound of the cluster border
      // compute s(jlow, i)
      sjlowi = ssq(jlow, i, sumX, sumXsq);
  
      ssqjlow = sjlowi + matrix[column - 1][jlow - 1];
  
      if (ssqjlow < matrix[column][i]) {
        // shrink the lower bound
        matrix[column][i] = ssqjlow;
        backtrackMatrix[column][i] = jlow;
      }
      jlow++;
  
      ssqj = sji + matrix[column - 1][j - 1];
      if (ssqj < matrix[column][i]) {
        matrix[column][i] = ssqj;
        backtrackMatrix[column][i] = j;
      }
    }
  
    fillMatrixColumn(imin, i - 1, column, matrix, backtrackMatrix, sumX, sumXsq);
    fillMatrixColumn(i + 1, imax, column, matrix, backtrackMatrix, sumX, sumXsq);
  }
  
  function fillMatrices(data, matrix, backtrackMatrix) {
    var nValues = matrix[0].length;
    var sumX = new Array(nValues);
    var sumXsq = new Array(nValues);
  
    // Use the median to shift values of x to improve numerical stability
    var shift = data[Math.floor(nValues / 2)];
  
    // Initialize first row in matrix & backtrackMatrix
    for (var i = 0; i < nValues; ++i) {
      if (i === 0) {
        sumX[0] = data[0] - shift;
        sumXsq[0] = (data[0] - shift) * (data[0] - shift);
      } else {
        sumX[i] = sumX[i - 1] + data[i] - shift;
        sumXsq[i] = sumXsq[i - 1] + (data[i] - shift) * (data[i] - shift);
      }
  
      // Initialize for k = 0
      matrix[0][i] = ssq(0, i, sumX, sumXsq);
      backtrackMatrix[0][i] = 0;
    }
  
    // Initialize the rest of the columns
    var imin;
    for (var k = 1; k < matrix.length; ++k) {
      if (k < matrix.length - 1) {
        imin = k;
      } else {
        // No need to compute matrix[K-1][0] ... matrix[K-1][N-2]
        imin = nValues - 1;
      }
  
      fillMatrixColumn(imin, nValues - 1, k, matrix, backtrackMatrix, sumX, sumXsq);
    }
  } */
  
  /**
   * Ckmeans clustering is an improvement on heuristic-based clustering
   * approaches like Jenks. The algorithm was developed in
   * [Haizhou Wang and Mingzhou Song](http://journal.r-project.org/archive/2011-2/RJournal_2011-2_Wang+Song.pdf)
   * as a [dynamic programming](https://en.wikipedia.org/wiki/Dynamic_programming) approach
   * to the problem of clustering numeric data into groups with the least
   * within-group sum-of-squared-deviations.
   *
   * Minimizing the difference within groups - what Wang & Song refer to as
   * `withinss`, or within sum-of-squares, means that groups are optimally
   * homogenous within and the data is split into representative groups.
   * This is very useful for visualization, where you may want to represent
   * a continuous variable in discrete color or style groups. This function
   * can provide groups that emphasize differences between data.
   *
   * Being a dynamic approach, this algorithm is based on two matrices that
   * store incrementally-computed values for squared deviations and backtracking
   * indexes.
   *
   * Unlike the [original implementation](https://cran.r-project.org/web/packages/Ckmeans.1d.dp/index.html),
   * this implementation does not include any code to automatically determine
   * the optimal number of clusters: this information needs to be explicitly
   * provided.
   *
   * ### References
   * _Ckmeans.1d.dp: Optimal k-means Clustering in One Dimension by Dynamic
   * Programming_ Haizhou Wang and Mingzhou Song ISSN 2073-4859
   *
   * from The R Journal Vol. 3/2, December 2011
   * @param {Array<number>} data input data, as an array of number values
   * @param {number} nClusters number of desired classes. This cannot be
   * greater than the number of values in the data array.
   * @returns {Array<Array<number>>} clustered input
   * @example
   * ckmeans([-1, 2, -1, 2, 4, 5, 6, -1, 2, -1], 3);
   * // The input, clustered into groups of similar numbers.
   * //= [[-1, -1, -1, -1], [2, 2, 2], [4, 5, 6]]);
   */
/*  function ckmeans(data, nClusters) {
    if (nClusters > data.length) {
      throw new Error('Cannot generate more classes than there are data values');
    }
  
    var nValues = data.length;
  
    var sorted = numericSort(data);
    // we'll use this as the maximum number of clusters
    var uniqueCount = uniqueCountSorted(sorted);
  
    // if all of the input values are identical, there's one cluster
    // with all of the input in it.
    if (uniqueCount === 1) {
      return [sorted[0]];
    }
    nClusters = Math.min(uniqueCount, nClusters);
  
    // named 'S' originally
    var matrix = makeMatrix(nClusters, nValues);
    // named 'J' originally
    var backtrackMatrix = makeMatrix(nClusters, nValues);
  
    // This is a dynamic programming way to solve the problem of minimizing
    // within-cluster sum of squares. It's similar to linear regression
    // in this way, and this calculation incrementally computes the
    // sum of squares that are later read.
    fillMatrices(sorted, matrix, backtrackMatrix);
  
    // The real work of Ckmeans clustering happens in the matrix generation:
    // the generated matrices encode all possible clustering combinations, and
    // once they're generated we can solve for the best clustering groups
    // very quickly.
    var clusters = [];
    var clusterRight = backtrackMatrix[0].length - 1;
  
    // Backtrack the clusters from the dynamic programming matrix. This
    // starts at the bottom-right corner of the matrix (if the top-left is 0, 0),
    // and moves the cluster target with the loop.
    for (var cluster = backtrackMatrix.length - 1; cluster >= 0; cluster--) {
      var clusterLeft = backtrackMatrix[cluster][clusterRight];
  
      // fill the cluster from the sorted input by taking a slice of the
      // array. the backtrack matrix makes this easy - it stores the
      // indexes where the cluster should start and end.
      clusters[cluster] = sorted[clusterLeft];
  
      if (cluster > 0) {
        clusterRight = clusterLeft - 1;
      }
    }
  
    return backtrackMatrix;
  } 
  
  
  //----------
  function initializeCentroids(data, k) {
    const uniqueValues = [...new Set(data)];
    const firstThreeUniqueValues = uniqueValues.slice(0, k);
    return firstThreeUniqueValues;
}

function assignDataPoints(data, centroids) {
  const assignments = new Array(data.length);

  for (let i = 0; i < data.length; i++) {
    const dataPoint = data[i];

    // Find the nearest centroid
    let nearestCentroid = null;
    let minDistance = Infinity;
    for (let j = 0; j < centroids.length; j++) {
      const centroid = centroids[j];
      const distance = Math.abs(dataPoint - centroid);

      if (distance < minDistance) {
        minDistance = distance;
        nearestCentroid = centroid;
      }
    }

    assignments[i] = nearestCentroid;
  }

  return assignments;
}

function updateCentroids(data, assignments, k, centroids) {
  const newCentroids = new Array(k).fill(0);
  const counts = new Array(k).fill(0);

  for (let i = 0; i < data.length; i++) {
    const dataPoint = data[i];
    const centroid = assignments[i];
    const centroidIndex = centroids.indexOf(centroid);

    newCentroids[centroidIndex] += dataPoint;
    counts[centroidIndex]++;
  }

  // Calculate the average for each centroid
  for (let i = 0; i < k; i++) {
    newCentroids[i] /= counts[i];
  }

  return newCentroids;
}

function centroidsAreEqual(centroids1, centroids2) {
  if (centroids1.length !== centroids2.length) {
    return false;
  }

  for (let i = 0; i < centroids1.length; i++) {
    if (centroids1[i] !== centroids2[i]) {
      return false;
    }
  }

  return true;
}


function borderKMeans(data, k, maxIterations) {
  // Step 1: Initialize centroids
  let centroids = initializeCentroids(data, k);

  for (let iter = 0; iter < maxIterations; iter++) {
    // Step 2: Assign data points to the nearest centroid
    const assignments = assignDataPoints(data, centroids);

    // Step 3: Update centroids
    const newCentroids = updateCentroids(data, assignments, k, centroids);

    // Step 4: Check for convergence
    if (centroidsAreEqual(centroids, newCentroids)) {
      break;
    }

    centroids = newCentroids;
  }

  return centroids;
}

function CKmeans2(data, k) {
  const n = data.length;
  const sortedData = data.slice().sort((a, b) => a - b); // Sort the data in ascending order

  // Initialize the clusters with the initial centroid positions
  const clusters = [];
  for (let i = 0; i < k; i++) {
    const centroidIndex = Math.floor((i * (n - 1)) / (k - 1));
    const centroid = sortedData[centroidIndex];
    clusters.push([centroid]);
  }

  let converged = false;
  while (!converged) {
    // Assign data points to the nearest cluster
    for (let i = 0; i < n; i++) {
      const dataPoint = sortedData[i];
      let minDistance = Infinity;
      let clusterIndex = 0;

      for (let j = 0; j < k; j++) {
        const centroid = clusters[j][0];
        const distance = Math.abs(dataPoint - centroid);

        if (distance < minDistance) {
          minDistance = distance;
          clusterIndex = j;
        }
      }

      clusters[clusterIndex].push(dataPoint);
    }

    // Update the centroids of each cluster
    let updatedCentroids = 0;
    for (let i = 0; i < k; i++) {
      const cluster = clusters[i];
      const oldCentroid = cluster[0];
      const sum = cluster.reduce((acc, val) => acc + val, 0);
      const newCentroid = sum / cluster.length;

      if (newCentroid !== oldCentroid) {
        cluster[0] = newCentroid;
        updatedCentroids++;
      }
    }

    // Check if the centroids have converged
    if (updatedCentroids === 0) {
      converged = true;
    } else {
      // Clear the cluster data points for the next iteration
      for (let i = 0; i < k; i++) {
        clusters[i] = [clusters[i][0]];
      }
    }
  }

  // Extract the final clusters without the centroid values
  const finalClusters = clusters.map(cluster => cluster.slice(1));

  // Calculate the means of each cluster
  const means = finalClusters.map(cluster => {
    const sum = cluster.reduce((acc, val) => acc + val, 0);
    return sum / cluster.length;
  });

  return { clusters: finalClusters, means };
}

function CKmeans_1d_dp(data, k) {
  const n = data.length;

  // Compute the squared distances between each pair of data points
  const distMatrix = [];
  for (let i = 0; i < n; i++) {
    distMatrix[i] = [];
    for (let j = 0; j < n; j++) {
      const diff = data[i] - data[j];
      distMatrix[i][j] = diff * diff;
    }
  }

  // Initialize the dynamic programming table
  const dp = [];
  for (let i = 0; i < n; i++) {
    dp[i] = [];
    for (let j = 0; j < k; j++) {
      dp[i][j] = i === 0 && j === 0 ? 0 : Infinity;
    }
  }

  // Compute the dynamic programming table
  for (let i = 0; i < n; i++) {
    let sum = 0;
    let sumSquares = 0;
    for (let j = i; j < n; j++) {
      sum += data[j];
      sumSquares += distMatrix[i][j];
      const cost = sumSquares - (sum * sum) / (j - i + 1);
      dp[j][0] = Math.min(dp[j][0], cost);
    }
  }

  // Compute the remaining dynamic programming table entries
  for (let j = 1; j < k; j++) {
    for (let i = j; i < n; i++) {
      let minCost = Infinity;
      let minIndex = 0;
      for (let p = j - 1; p < i; p++) {
        const cost = dp[p][j - 1] + dp[i][0];
        if (cost < minCost) {
          minCost = cost;
          minIndex = p;
        }
      }
      dp[i][j] = minCost;
      dp[i][j] = Math.min(dp[i][j], dp[i - 1][j]);
    }
  }

  // Find the cluster boundaries
  const clusterIndices = [];
    let i = n - 1;
    for (let j = k - 1; j >= 0; j--) {
    clusterIndices[j] = i;
    if (j > 0) {
        let minValue = Infinity;
        for (let p = j - 1; p < i; p++) {
        if (dp[p][j - 1] !== undefined) {
            const cost = dp[p][j - 1] + dp[i][0];
            if (cost < minValue) {
            minValue = cost;
            i = p;
            }
        }
        }
    } else {
        i = Math.floor(dp[i][j]) - 1;
    }
    }

  // Assign data points to clusters
  const clusters = [];
  const means = [];
  for (let j = 0; j < k; j++) {
    const startIndex = j > 0 ? clusterIndices[j - 1] + 1 : 0;
    const endIndex = clusterIndices[j];
    const cluster = data.slice(startIndex, endIndex + 1);
    const mean = cluster.reduce((a, b) => a + b, 0) / cluster.length;
    clusters.push(cluster);
    means.push(mean);
  }

  return { clusters, means };
}
  */

export default {
    //ckmeans,
    K_means,
    role,
    equal,
    ifArray,
    less_than,
    MoreMenus,
    authorized,
    findElement,
    isValidJson,
    greater_than,
    getTaskForce,
    resizedElement,
    less_than_equal,
    greater_than_equal,
    isValidJsonAndEmpty,
    documentContextMenus,
}
