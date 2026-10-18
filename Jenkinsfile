@Library('deployment-library@main') _

// tugas-fikri — DEV (namespace portofolio, NodePort 31176). Runtime env from Vault portofolio/tugas-fikri via ESO.
deployPipelineVaultEso(
    gitRepository: 'https://github.com/enrichoalkalas01/tugas-fikri.git',
    gitBranch: 'master',
    gitCredentialsId: 'github-credentials',

    registryUrl: '192.168.20.21',
    registryCredentialsId: 'harbor-credentials',
    imageName: 'tugas-fikri',
    imageFolder: 'other',

    namespace: 'portofolio',
    helmValuesFile: 'values/values.dev.yml',
    servicePort: '31176',

    vaultRuntimePath: 'tugas-fikri',
    vaultRuntimeStoreName: 'vault-backend',
    vaultRuntimeRefreshInterval: '1m',
)
