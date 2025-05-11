<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Contract Analyzer</title>
    <script src="https://cdn.jsdelivr.net/npm/vis-network@9.1.2/dist/vis-network.min.js"></script>
    <style>
        :root {
            --primary: #4e44ce;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --danger: #dc3545;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        header h1 {
            margin: 0;
            text-align: center;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-bottom: none;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
            transition: all 0.3s;
        }
        
        .tab.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .tab-content {
            display: none;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #3a36b5;
        }
        
        #graph {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 20px;
        }
        
        .analysis-result {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            white-space: pre-wrap;
            font-family: monospace;
        }
        
        .tracked-contracts {
            margin-top: 20px;
        }
        
        .contract-item {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .contract-address {
            font-family: monospace;
            color: var(--primary);
        }
        
        .remove-btn {
            background-color: var(--danger);
            padding: 5px 10px;
            font-size: 14px;
        }
        
        .node-current {
            background-color: #ffeb3b !important;
            border: 2px solid #fbc02d !important;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        .contract-container {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .contract-address {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .section-title {
            font-weight: bold;
            margin: 15px 0 10px 0;
            color: #3498db;
        }
        .address-list {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .address-item {
            font-family: monospace;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .address-item:last-child {
            border-bottom: none;
        }
        .empty-list {
            color: #7f8c8d;
            font-style: italic;
        }
        .date-changed {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Smart Contract Analyzer</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="tabs">
            <div class="tab active" data-tab="dependencies">Dependency Analysis</div>
            <div class="tab" data-tab="code">Code Analysis</div>
            <div class="tab" data-tab="tracking">Tracked Contracts</div>
            <div id="notiTab" class="tab" data-tab="notifications">Notifications</div>
        </div>
        
        <!-- Dependency Analysis Tab -->
        <div id="dependencies" class="tab-content active">
            <h2>Contract Dependency Analysis</h2>
            <div class="form-group">
                <label for="contractAddress">Contract Address:</label>
                <input type="text" id="contractAddress" placeholder="Enter contract address (0x...)" />
            </div>
            <button id="analyzeDependencies">Analyze Dependencies</button>
            <button id="trackContract" style="background-color: var(--success); margin-left: 10px;">Track This Contract</button>
            <div id="graph"><svg width="800" height="600"></svg></div>
            <div id="loading" style="display: none;">Analyzing dependencies, please wait...</div>
        </div>
        
        <!-- Code Analysis Tab -->
        <div id="code" class="tab-content">
            <h2>Contract Code Analysis</h2>
            <div class="form-group">
                <label for="codeContractAddress">Contract Address:</label>
                <input type="text" id="codeContractAddress" placeholder="Enter contract address (0x...)" />
            </div>
            <button id="analyzeCode">Analyze Code</button>
            
            <div id="codeAnalysisResult" class="analysis-result" style="display: none;">
                <!-- Analysis results will appear here -->
            </div>
        </div>
        
        <!-- Tracked Contracts Tab -->
        <div id="tracking" class="tab-content">
            <h2>Tracked Contracts</h2>
            <div id="trackedContractsList" class="tracked-contracts">
                <!-- Tracked contracts will appear here -->
                <p>No contracts being tracked yet.</p>
            </div>
        </div>

        <div id="notifications" class="tab-content">
            <h2>Notifications of subscribing contracts</h2>
            <div id="notifications" class="tracked-contracts">
                <!-- Tracked contracts will appear here -->
                <p>No notifications yet</p>
            </div>
        </div>
    </div>

    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
       
        mock_user_id = 1
        async function fetchGraphData() {
          try {
              const contractAddress = document.getElementById('contractAddress').value;
              // console.log(contractAddress);
              // const response = await fetch(`http://localhost:8000/getGraph/${contractAddress}`);
              const response = await fetch(`http://localhost:8000/getGraph/${contractAddress}`, { signal: AbortSignal.timeout(20000) });
              
              if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
              }
              
              const graphData = await response.json();
              console.log('Graph data received:', graphData);
              return graphData;
          } catch (error) {
              console.error('Error fetching graph data:', error);
              return null;
          }
        }

        notif = async () => {
            notifications = await fetch('/getNotifications/1');
            notifications = await notifications.json();
            console.log(notifications);
            const container = document.getElementById('notifications');
            notifications.forEach(contract => {
                // Парсим JSON из changed_data
                const changedData = JSON.parse(contract.changed_data);
                
                // Создаем HTML для контракта
                const contractDiv = document.createElement('div');
                contractDiv.className = 'contract-container';
                
                // Добавляем адрес контракта
                contractDiv.innerHTML += `
                    <div class="contract-address">Contract Address: ${contract.contract}</div>
                    <div class="date-changed">Date changed: ${contract.date_changed}</div>
                `;
                
                // Добавляем секцию с добавленными адресами
                contractDiv.innerHTML += `
                    <div class="section-title">Added Addresses:</div>
                    <div class="address-list" id="added-${contract.contract}">
                        ${changedData.added_nodes.length > 0 ? 
                            changedData.added_nodes.map(addr => `<div class="address-item">${addr}</div>`).join('') : 
                            '<div class="empty-list">No addresses added</div>'}
                    </div>
                `;
                
                // Добавляем секцию с удаленными адресами
                contractDiv.innerHTML += `
                    <div class="section-title">Removed Addresses:</div>
                    <div class="address-list" id="removed-${contract.contract}">
                        ${changedData.removed_nodes.length > 0 ? 
                            changedData.removed_nodes.map(addr => `<div class="address-item">${addr}</div>`).join('') : 
                            '<div class="empty-list">No addresses removed</div>'}
                    </div>
                `;
                
                container.appendChild(contractDiv);
            });
        };

        notif();
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and content
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                tab.classList.add('active');
                const tabId = tab.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Dependency Analysis
        document.getElementById('analyzeDependencies').addEventListener('click', analyzeDependencies);
        document.getElementById('trackContract').addEventListener('click', trackContract);
        
        // Code Analysis
        document.getElementById('analyzeCode').addEventListener('click', analyzeCode);
        // 0x7a250d5630b4cf539739df2c5dacb4c659f2488d
        // Tracked contracts from localStorage
        let trackedContracts = JSON.parse(localStorage.getItem('trackedContracts')) || [];
        // updateTrackedContractsList();
        
        async function analyzeDependencies() {
            const contractAddress = document.getElementById('contractAddress').value.trim();
            if (!contractAddress || !contractAddress.startsWith('0x')) {
                alert('Please enter a valid contract address');
                return;
            }
            
            document.getElementById('loading').style.display = 'block';
            
            // Simulate API call (in a real app, this would be a fetch to your backend)
            // setTimeout(() => {
                // Mock data - in a real app, this would come from your API
            const mockData = await generateMockDependencies(contractAddress);
            console.log('mockData:', mockData);
            await renderDependencyGraph(contractAddress.toLowerCase(), mockData);
            document.getElementById('loading').style.display = 'none';
            // }, 1000);
        }
        
        async function trackContract() {
            const contractAddress = document.getElementById('contractAddress').value.trim().toLowerCase();
            if (!contractAddress || !contractAddress.startsWith('0x')) {
                alert('Please enter a valid contract address');
                return;
            }
            
            response = await fetch(`/subscribe/1/${contractAddress}`);

            // fetch('/subscribedContracts/1');

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            updateTrackedContractsList();

            // if (!trackedContracts.includes(contractAddress)) {
            //     trackedContracts.push(contractAddress);
            //     localStorage.setItem('trackedContracts', JSON.stringify(trackedContracts));
            //     updateTrackedContractsList();
            //     alert('Contract added to tracking list');
            // } else {
            //     alert('Contract is already being tracked');
            // }
        }
        
        async function analyzeCode() {
            const contractAddress = document.getElementById('codeContractAddress').value.trim();
            if (!contractAddress || !contractAddress.startsWith('0x')) {
                alert('Please enter a valid contract address');
                return;
            }
            responce = await fetch(`/smart_contract_analize/${contractAddress}`);
            let responce2 = await responce.text();

            console.log('ответтаков');
            console.log(responce2);


            // Simulate API call
            setTimeout(() => {
                const analysisResult = `Code analysis for contract ${contractAddress}:
                
                - Contract Name: ExampleContract
                - Compiler Version: v0.8.20+commit.a1b79de6
                - Optimization Enabled: Yes
                - Runs: 200
                - Verified: Yes
                - License: MIT
                
                Security Analysis:
                - No reentrancy vulnerabilities detected
                - No integer overflow/underflow detected
                - No unprotected functions found
                
                Code Quality:
                - Well-structured code
                - Proper use of modifiers
                - Good documentation
                
                Dependencies:
                - @openzeppelin/contracts: 4.8.1
                - SafeMath library used`;
                console.log('unicode');
                // console.log(String.fromCharCode(responce2));
                const resultDiv = document.getElementById('codeAnalysisResult');
                console.log('responce text');
                // console.log(responce.text);
                resultDiv.textContent = responce2;
                resultDiv.style.display = 'block';
            }, 800);
        }

        function parseChangedContracts(data) {
            // data.added_nodes.forEach((addr))
        }

        function subContractsStatusParse(data) {
            text =  `<span class="contract-address">данные не изменились</span>`;
        
            if (data != null) {
                if (data.add_no)
                `<span class="contract-address">добавленные контракты</span>`;
            }
        }
        
        async function updateTrackedContractsList() {
            const container = document.getElementById('trackedContractsList');
            
            response = await fetch('/subscribedContracts/1');
            // if (!response.ok) {
            //     throw new Error(`HTTP error! status: ${response.status}`);
            // }
            data = await response.json();
            console.log('data response:');
            console.log(response.json());

            if (trackedContracts.length === 0) {
                container.innerHTML = '<p>No contracts being tracked yet.</p>';
                return;
            }
            
            // if (data.change_data != null) {
            //     <span class="contract-address"></span>
            // }

            container.innerHTML = '';
            changesInfo = getText(data.change_data);
            trackedContracts.forEach(contract => {
                const div = document.createElement('div');
                div.className = 'contract-item';
                div.innerHTML = `
                    <span class="contract-address">${data.address}</span>
                    <span class="contract-address">${data.change_data}</span>
                    // <button class="remove-btn" data-address="${contract}">Remove</button>
                `;
                container.appendChild(div);
            });
            
            // Add event listeners to remove buttons
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const address = e.target.getAttribute('data-address');
                    trackedContracts = trackedContracts.filter(c => c !== address);
                    localStorage.setItem('trackedContracts', JSON.stringify(trackedContracts));
                    updateTrackedContractsList();
                });
            });
        }

        const copyTooltip = document.createElement('div');
        copyTooltip.className = 'copy-tooltip';
        document.body.appendChild(copyTooltip);

        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            // Показываем тултип
            copyTooltip.textContent = 'Copied!';
            copyTooltip.style.opacity = '1';
            copyTooltip.style.left = (event.pageX + 10) + 'px';
            copyTooltip.style.top = (event.pageY + 10) + 'px';
            
            // Скрываем тултип через 2 секунды
            setTimeout(() => {
                copyTooltip.style.opacity = '0';
            }, 2000);
        }
        
        function renderDependencyGraph(mainContract, dependencies) {
            const container = document.getElementById('graph');
            container.innerHTML = ''; // Очищаем предыдущий граф

            const nodes = new vis.DataSet();
            const edges = new vis.DataSet();

            // 1. Всегда добавляем главный контракт первым
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

            // 2. Сначала собираем все уникальные узлы
            const allNodes = new Set([mainContract]);
            dependencies.forEach(dep => {
                allNodes.add(dep.address);
                if (dep.from !== 'main') allNodes.add(dep.from);
            });

            // 3. Добавляем все узлы
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

            // 4. Добавляем связи
            dependencies.forEach(dep => {
                const from = dep.from === 'main' ? mainContract : dep.from;
                
                // Проверяем, что оба узла существуют
                if (nodes.get(from) && nodes.get(dep.address)) {
                    edges.add({
                        from: from,
                        to: dep.address,
                        arrows: 'to'
                    });
                }
            });

            // 5. Упрощенные настройки физики
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
                    font: { size: 12 }
                },
                edges: {
                    arrows: 'to',
                    smooth: { type: 'continuous' }
                },
                interaction: {
                    hover: true
                }
            };

            // Создаем сеть
            const network = new vis.Network(container, { nodes, edges }, options);
            
            // Добавляем обработчик клика по узлу для копирования адреса
            network.on("click", function(params) {
                if (params.nodes.length > 0) {
                    const nodeId = params.nodes[0];
                    const node = nodes.get(nodeId);
                    if (node) {
                        copyToClipboard(nodeId);
                        
                        // Временно выделяем скопированный узел
                        nodes.update({
                            id: nodeId,
                            color: {
                                background: '#4CAF50',
                                border: '#388E3C'
                            }
                        });
                        
                        // Возвращаем исходный цвет через 1 секунду
                        setTimeout(() => {
                            if (nodeId === mainContract) {
                                nodes.update({
                                    id: nodeId,
                                    color: {
                                        background: '#FFEB3B',
                                        border: '#FBC02D'
                                    }
                                });
                            } else {
                                nodes.update({
                                    id: nodeId,
                                    color: {
                                        background: '#D2E5FF',
                                        border: '#97C1FF'
                                    }
                                });
                            }
                        }, 1000);
                    }
                }
            });
            
            // Добавляем контекстное меню для узлов
            network.on("oncontext", function(params) {
                params.event.preventDefault();
                if (params.nodes.length > 0) {
                    const nodeId = params.nodes[0];
                    const node = nodes.get(nodeId);
                    if (node) {
                        copyToClipboard(nodeId);
                    }
                }
                return false;
            });
            
            // Показываем полный адрес при наведении
            network.on("hoverNode", function(params) {
                const nodeId = params.node;
                const node = nodes.get(nodeId);
                if (node) {
                    copyTooltip.textContent = nodeId;
                    copyTooltip.style.left = (params.event.center.x + 10) + 'px';
                    copyTooltip.style.top = (params.event.center.y + 10) + 'px';
                    copyTooltip.style.opacity = '1';
                }
            });
            
            network.on("blurNode", function() {
                copyTooltip.style.opacity = '0';
            });
        }
        
        // Helper function to generate mock dependency data
        async function generateMockDependencies(mainContract) {
          mainContract = mainContract.toLowerCase();
          try {
              // Получаем данные с сервера
              const response = await fetch(`/getGraph/${mainContract}`);
            
              if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
              }
              
              // Парсим JSON-ответ
              const graphData = await response.json();
              console.log('data');
              console.log(graphData);

              if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
              }

              
              // Преобразуем данные в нужный формат
              const dependencies = [];
              console.log('main co');
              console.log(graphData[mainContract]);
              // Добавляем связи для главного контракта
              if (graphData[mainContract]) {
                  graphData[mainContract].forEach((target) => {
                      dependencies.push({
                          from: 'main',
                          address: target
                      });
                  });
              }
              
              // Добавляем все остальные связи из графа
              for (const [source, targets] of Object.entries(graphData)) {
                  // Пропускаем главный контракт, так как его связи уже добавили
                  if (source.toLowerCase() === mainContract.toLowerCase()) continue;
                  
                  targets.forEach((target) => {
                      dependencies.push({
                          from: source,
                          address: target
                      });
                  });
              }
              
              console.log('Processed dependencies:', dependencies);
              return dependencies;
              
          } catch (error) {
              console.error('Error fetching and processing graph data:', error);
              
              // Возвращаем мок-данные в случае ошибки
              // const mockContracts = [
              //     '0x7a250d5630B4cF539739dF2C5dAcb4c659F2488D',
              //     '0xC02aaA39b223FE8D0A0e5C4F27eAD9083C756Cc2',
              //     '0xdAC17F958D2ee523a2206206994597C13D831ec7',
              //     '0x6B175474E89094C44Da98b954EedeAC495271d0F',
              //     '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48'
              // ];
              
              // return [
              //     { from: 'main', address: mockContracts[0] },
              //     { from: 'main', address: mockContracts[1] },
              //     { from: mockContracts[0], address: mockContracts[2] },
              //     { from: mockContracts[1], address: mockContracts[3] },
              //     { from: mockContracts[1], address: mockContracts[4] }
              // ];
          }
    }

    async function getNotifications() {
        fetch()
    }
    </script>
</body>
</html>