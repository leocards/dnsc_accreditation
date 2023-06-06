<template>
    <div class="w-full">
        <div class="rounded w-full bg-gray-200 hover:bg-gray-300/80 transition_300 hover:dark:bg-white/20 dark:bg-tertiaryDarkBg h-11 mb-1 flex items-center justify-between px-3 cursor-pointer"
        :id="`analytic${survey.surveyId}`" 
        @click.self="clusterAccred">
            <div class="pointer-events-none">{{`${survey.program} ${survey.level}`}} <span v-if="survey.rate">- {{survey.rate}} </span></div>

            <button @click="reloadArea" class="text-xs p-0.5 px-1 rounded transition_300 hover:bg-dnscGreen hover:text-white">
                reload
            </button>
        </div>

        <div 
            class="w-full pl-6 relative text-xs"
            v-for="(area, index) in areas" 
            v-if="areas.length > 0 && (showAreas && !LoadData)"
        >
            <div class="absolute top-0 left-2.5 h-[1.50rem] w-3.5 rounded-bl border-b-2 border-l-2 border-green-600"></div>
            <div class="absolute top-0 left-2.5 h-full w-5 border-l-2 border-green-600" 
            v-if="(areas.length-1) != index"></div>

            <div class="pb-1">
                <div 
                    class="rounded w-full cursor-pointer transition_300 h-11 flex items-center justify-between px-3"
                    :class="[selectedArea == area.id? 'dark:bg-dnscGreen bg-dnscGreen/20 text-dnscGreen dark:text-white' : 'bg-gray-200 hover:bg-gray-300/80 hover:dark:bg-white/20 dark:bg-tertiaryDarkBg']"
                    @click="selectAreaCluster(area)"
                >
                    <div>{{area.title}} {{area.description}}</div>
                    <!-- <div class="flex justify-between max-w-[9rem]">
                        <div>show more</div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import { ref } from "@vue/reactivity"
import { onMounted, onUnmounted } from '@vue/runtime-core'

const areas = ref([])
const showAreas = ref(false)
const LoadData = ref(false)
const selectedArea = ref(null)
const clusterForm = useForm({
    clus: null,
    clus2: null,
    allClus: null
})
const clusteredData = ref([])


const props = defineProps({
    survey: Object,
    errorCluster: Boolean
})

const emits = defineEmits([
    'handleClusters',
    'handleAccred',
    'handleloadCluster',
    'handleLoadData',
    'handleError'
])

const winClick = e => {
    if(!e.target.closest('.analytics')){
        getClickedOutside()
    }
}
const getClickedOutside = () => {
    showAreas.value = false
    emits('handleAccred', showAreas.value)
}

const getAreas = async (show = false, levelId) => {
    try{

        let res = await axios.get(`/getLevelAreas/${levelId}`)

        areas.value = res.data.areas

        let rates = res.data.rates

        clusterForm.allClus = rates

        clusterForm.clus = JSON.stringify(rates.filter((rate) => {
            return rate.isCluster
        }))

        clusterForm.clus2 = rates.filter((rate) => {
            return rate.isCluster
        })

        return true
    } catch (e) {
        return false
    }
}

// Start CKmeans-----------------

function numericSort(array) {
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
}


function ckmeans(data, nClusters) {
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
    clusters[cluster] = sorted.slice(clusterLeft, clusterRight + 1);

    if (cluster > 0) {
      clusterRight = clusterLeft - 1;
    }
  }

  return clusters;
}
// end CKmeans----------------

function roundToFourDecimals(number) {
  return parseFloat(number.toFixed(4));
}

function generateCLuster(data, k) {
    let clustered = {
        area: data.area,
        best: null,
        avg: null,
        improve: null,
        means: null
    }

    //call the CKmeans
    let clusters = ckmeans(data.ind, k)

    clustered.improve = clusters[0]
    clustered.avg = clusters[1]
    clustered.best = clusters[2]

    clustered.means = clusters.map((cluster) => {
        return roundToFourDecimals(cluster.reduce((sum, value) => sum + value, 0) / cluster.length);
    });

    return clustered
}

const clusterAccred = async () => {
    if(areas.value.length == 0){
        emits('handleLoadData', true)
        LoadData.value = true
        selectedArea.value = null

        let areas = await getAreas(false, props.survey.surveyId)
        
        if(areas){
            try {
                clusterForm.clus2.forEach(element => {
                    let res = generateCLuster(element, 3)
                    clusteredData.value.push(res)
                });

                showAreas.value = true
                LoadData.value = false
    
                emits('handleLoadData', false)

            } catch (e) {
                console.log(e)
            }
        }
    }else{
        showAreas.value = !showAreas.value
        LoadData.value = false
    }
}

const reloadArea = async () => {
    areas.value = []
    clusterAccred()
}

const selectAreaCluster = area => {

    let cannotCluster = clusterForm.allClus.find(val => {
      return area.id == val.area && val.isCluster
    })

    if(cannotCluster){
      selectedArea.value = area.id
      let areaData = areas.value.find(el => {
          return el.id == area.id
      })
  
      let clusters = clusteredData.value.find(el => {
          return el.area == area.id
      })

      emits('handleClusters', {clusters, areaData})
    } else {
      emits('handleError', 'Unable to generate')
    }


}

onMounted(() => {
    window.addEventListener('click', winClick)
})
onUnmounted(() => {
    window.removeEventListener('click', winClick)
})
</script>