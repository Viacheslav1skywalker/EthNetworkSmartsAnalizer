<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Previous head content remains the same -->
    <!-- Add this to your existing head section -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <!-- Previous HTML content remains the same until the tracked contracts tab -->
    
    <!-- Tracked Contracts Tab - Updated -->
    <div id="tracking" class="tab-content">
        <h2>Tracked Contracts</h2>
        <div class="form-group">
            <label for="userId">Your User ID:</label>
            <input type="text" id="userId" placeholder="Enter your user ID" />
            <button id="loadSubscriptions">Load Subscriptions</button>
        </div>
        <div id="trackedContractsList" class="tracked-contracts">
            <p>No contracts being tracked yet. Enter your user ID and click "Load Subscriptions".</p>
        </div>
    </div>

    <!-- Add this modal for contract details -->
    <div id="contractModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background-color: white; padding: 20px; border-radius: 5px; max-width: 600px; max-height: 80vh; overflow-y: auto;">
            <h2 id="modalContractAddress"></h2>
            <div id="modalContractContent" class="analysis-result"></div>
            <button id="modalAnalyzeBtn" style="margin-top: 10px;">Analyze Changes</button>
            <button id="closeModal" style="margin-top: 10px; background-color: var(--danger);">Close</button>
        </div>
    </div>

    <script>
        // Add these variables at the top of your script
        let currentModalContract = null;
        let currentGraphData = null;
        let currentMainContract = null;

        // Add this after your existing event listeners
        document.getElementById('loadSubscriptions').addEventListener('click', loadSubscriptions);
        document.getElementById('modalAnalyzeBtn').addEventListener('click', analyzeContractChanges);
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('contractModal').style.display = 'none';
        });

        // Updated trackContract function to use your backend
        async function trackContract() {
            const contractAddress = document.getElementById('contractAddress').value.trim();
            const userId = document.getElementById('userId').value.trim();
            
            if (!contractAddress || !contractAddress.startsWith('0x')) {
                alert('Please enter a valid contract address');
                return;
            }
            
            if (!userId) {
                alert('Please enter your user ID first');
                return;
            }
            
            try {
                const response = await axios.post(`/api/subscribeContract/${userId}/${contractAddress}`);
                if (response.status === 200) {
                    alert('Contract subscription successful!');
                    loadSubscriptions();
                } else {
                    alert('Failed to subscribe to contract');
                }
            } catch (error) {
                console.error('Subscription error:', error);
                alert('Error subscribing to contract: ' + error.message);
            }
        }

        // New function to load subscribed contracts
        async function loadSubscriptions() {
            const userId = document.getElementById('userId').value.trim();
            if (!userId) {
                alert('Please enter your user ID');
                return;
            }
            
            try {
                const response = await axios.get(`/api/getSubContracts/${userId}`);
                const contracts = response.data;
                
                const container = document.getElementById('trackedContractsList');
                
                if (!contracts || contracts.length === 0) {
                    container.innerHTML = '<p>No contracts being tracked yet.</p>';
                    return;
                }
                
                container.innerHTML = '';
                contracts.forEach(contract => {
                    const div = document.createElement('div');
                    div.className = 'contract-item';
                    div.innerHTML = `
                        <span class="contract-address">${contract.address}</span>
                        <div>
                            <button class="analyze-btn" data-address="${contract.address}">Analyze</button>
                            <button class="remove-btn" data-address="${contract.address}">Remove</button>
                        </div>
                    `;
                    container.appendChild(div);
                });
                
                // Add event listeners to analyze buttons
                document.querySelectorAll('.analyze-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const address = e.target.getAttribute('data-address');
                        showContractDetails(address);
                    });
                });
                
                // Add event listeners to remove buttons
                document.querySelectorAll('.remove-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const address = e.target.getAttribute('data-address');
                        removeTrackedContract(address);
                    });
                });
                
            } catch (error) {
                console.error('Error loading subscriptions:', error);
                document.getElementById('trackedContractsList').innerHTML = 
                    '<p>Error loading subscriptions. Please try again.</p>';
            }
        }

        // Function to show contract details in modal
        async function showContractDetails(address) {
            currentModalContract = address;
            document.getElementById('modalContractAddress').textContent = address;
            document.getElementById('modalContractContent').innerHTML = 'Loading contract details...';
            document.getElementById('contractModal').style.display = 'flex';
            
            try {
                // Fetch basic contract info
                const response = await axios.get(`/api/contractInfo/${address}`);
                const contractInfo = response.data;
                
                let infoHTML = `
                    <h3>Basic Information</h3>
                    <p><strong>Creator:</strong> ${contractInfo.creator || 'Unknown'}</p>
                    <p><strong>Creation Date:</strong> ${contractInfo.creationDate || 'Unknown'}</p>
                    <p><strong>Last Activity:</strong> ${contractInfo.lastActivity || 'Unknown'}</p>
                    
                    <h3>Current Dependencies</h3>
                `;
                
                if (contractInfo.dependencies && contractInfo.dependencies.length > 0) {
                    infoHTML += '<ul>';
                    contractInfo.dependencies.forEach(dep => {
                        infoHTML += `<li>${dep}</li>`;
                    });
                    infoHTML += '</ul>';
                } else {
                    infoHTML += '<p>No dependencies found</p>';
                }
                
                document.getElementById('modalContractContent').innerHTML = infoHTML;
                
            } catch (error) {
                console.error('Error fetching contract info:', error);
                document.getElementById('modalContractContent').innerHTML = 
                    'Failed to load contract details. Please try again.';
            }
        }

        // Function to analyze contract changes
        async function analyzeContractChanges() {
            if (!currentModalContract) return;
            
            const modalContent = document.getElementById('modalContractContent');
            modalContent.innerHTML = 'Analyzing changes...';
            
            try {
                const response = await axios.get(`/api/analyzeChanges/${currentModalContract}`);
                const analysis = response.data;
                
                let analysisHTML = `
                    <h3>Change Analysis</h3>
                    <p><strong>Last Analyzed:</strong> ${new Date(analysis.timestamp).toLocaleString()}</p>
                    
                    <h4>Dependency Changes</h4>
                `;
                
                if (analysis.dependencyChanges.added.length > 0) {
                    analysisHTML += `
                        <p><strong>Added Dependencies:</strong></p>
                        <ul>
                            ${analysis.dependencyChanges.added.map(dep => `<li>${dep}</li>`).join('')}
                        </ul>
                    `;
                }
                
                if (analysis.dependencyChanges.removed.length > 0) {
                    analysisHTML += `
                        <p><strong>Removed Dependencies:</strong></p>
                        <ul>
                            ${analysis.dependencyChanges.removed.map(dep => `<li>${dep}</li>`).join('')}
                        </ul>
                    `;
                }
                
                if (analysis.dependencyChanges.added.length === 0 && analysis.dependencyChanges.removed.length === 0) {
                    analysisHTML += '<p>No dependency changes detected since last analysis.</p>';
                }
                
                analysisHTML += `
                    <h4>Code Changes</h4>
                    <p><strong>Code Hash:</strong> ${analysis.codeHash}</p>
                `;
                
                if (analysis.codeChanged) {
                    analysisHTML += '<p style="color: var(--danger);">Code has changed since last analysis!</p>';
                } else {
                    analysisHTML += '<p style="color: var(--success);">No code changes detected.</p>';
                }
                
                modalContent.innerHTML += analysisHTML;
                
            } catch (error) {
                console.error('Error analyzing changes:', error);
                modalContent.innerHTML += '<p style="color: var(--danger);">Error analyzing changes. Please try again.</p>';
            }
        }

        // Function to remove a tracked contract
        async function removeTrackedContract(address) {
            const userId = document.getElementById('userId').value.trim();
            if (!userId) {
                alert('Please enter your user ID first');
                return;
            }
            
            if (confirm(`Are you sure you want to stop tracking contract ${address}?`)) {
                try {
                    const response = await axios.delete(`/api/unsubscribeContract/${userId}/${address}`);
                    if (response.status === 200) {
                        alert('Contract removed from tracking');
                        loadSubscriptions();
                    }
                } catch (error) {
                    console.error('Error removing contract:', error);
                    alert('Failed to remove contract: ' + error.message);
                }
            }
        }

        // Updated renderDependencyGraph function to include node hover info
        function renderDependencyGraph(mainContract, dependencies) {
            currentMainContract = mainContract;
            currentGraphData = dependencies;
            
            const container = document.getElementById('graph');
            container.innerHTML = '';

            const nodes = new vis.DataSet();
            const edges = new vis.DataSet();

            // Add main contract node
            nodes.add({
                id: mainContract,
                label: mainContract.slice(0, 8) + '...' + mainContract.slice(-4),
                title: mainContract,
                color: {
                    background: '#FFEB3B',
                    border: '#FBC02D',
                    highlight: {
                        background: '#FFF59D',
                        border: '#FBC02D'
                    }
                },
                borderWidth: 2,
                shape: 'box'
            });

            // Collect all unique nodes
            const allNodes = new Set([mainContract]);
            dependencies.forEach(dep => {
                allNodes.add(dep.address);
                if (dep.from !== 'main') allNodes.add(dep.from);
            });

            // Add all nodes
            allNodes.forEach(address => {
                if (address !== mainContract) {
                    nodes.add({
                        id: address,
                        label: address.slice(0, 8) + '...' + address.slice(-4),
                        title: address,
                        shape: 'box'
                    });
                }
            });

            // Add edges
            dependencies.forEach(dep => {
                const from = dep.from === 'main' ? mainContract : dep.from;
                if (nodes.get(from) && nodes.get(dep.address)) {
                    edges.add({
                        from: from,
                        to: dep.address,
                        arrows: 'to'
                    });
                }
            });

            // Network options
            const options = {
                physics: {
                    enabled: true,
                    solver: 'forceAtlas2Based',
                    forceAtlas2Based: {
                        gravitationalConstant: -50,
                        centralGravity: 0.01,
                        springLength: 100,
                        springConstant: 0.08
                    },
                    stabilization: {
                        iterations: 250
                    }
                },
                nodes: {
                    shape: 'box',
                    font: { size: 12 },
                    shadow: true
                },
                edges: {
                    arrows: 'to',
                    smooth: { type: 'continuous' },
                    shadow: true
                },
                interaction: {
                    hover: true,
                    tooltipDelay: 200
                }
            };

            // Create the network
            const network = new vis.Network(container, { nodes, edges }, options);

            // Add click event for nodes to show details
            network.on("click", function(params) {
                if (params.nodes.length > 0) {
                    const nodeId = params.nodes[0];
                    showContractDetails(nodeId);
                }
            });

            // Add hover event for nodes to show tooltip
            network.on("hoverNode", function(params) {
                const nodeId = params.node;
                const node = nodes.get(nodeId);
                
                // You can customize this tooltip with more info
                const tooltip = document.createElement('div');
                tooltip.className = 'network-tooltip';
                tooltip.innerHTML = `
                    <strong>Contract:</strong> ${nodeId}<br>
                    <small>Click for details</small>
                `;
                
                // Position the tooltip near the cursor
                tooltip.style.position = 'absolute';
                tooltip.style.left = params.pointer.DOM.x + 'px';
                tooltip.style.top = params.pointer.DOM.y + 'px';
                tooltip.style.background = 'white';
                tooltip.style.padding = '5px';
                tooltip.style.border = '1px solid #ccc';
                tooltip.style.borderRadius = '3px';
                tooltip.style.boxShadow = '0 0 5px rgba(0,0,0,0.2)';
                tooltip.style.zIndex = '100';
                
                document.body.appendChild(tooltip);
                
                // Remove tooltip when mouse leaves
                network.on("blurNode", function() {
                    if (document.body.contains(tooltip)) {
                        document.body.removeChild(tooltip);
                    }
                });
            });
        }

        // Add this CSS for the tooltip
        const style = document.createElement('style');
        style.textContent = `
            .network-tooltip {
                position: absolute;
                background-color: white;
                padding: 5px 10px;
                border-radius: 3px;
                border: 1px solid #ddd;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                pointer-events: none;
                z-index: 100;
                font-size: 12px;
                max-width: 200px;
            }
            
            #contractModal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                z-index: 1000;
                justify-content: center;
                align-items: center;
            }
            
            #contractModal > div {
                background-color: white;
                padding: 20px;
                border-radius: 5px;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>