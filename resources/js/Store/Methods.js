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

export default {
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
