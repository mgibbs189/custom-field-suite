module.exports = {
  testEnvironment: 'jsdom',
  roots: ['<rootDir>/assets/js/fields/'],
  testMatch: [
    '**/__tests__/**/*.js',
    '**/?(*.)+(spec|test).js'
  ],
  moduleFileExtensions: ['js'],
  transform: {
    '^.+\\.js$': 'babel-jest'
  },
  setupFiles: ['<rootDir>/jest.setup.js'],
  collectCoverage: true,
  coverageDirectory: 'coverage',
  collectCoverageFrom: [
    'assets/js/fields/**/*.js',
    '!assets/js/fields/**/*.test.js',
    '!assets/js/fields/**/__tests__/**'
  ],
  verbose: true
};
